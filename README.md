# JWT + Shibboleth API — Demo Symfony 7.4

API stateless protégée par JWT (RS256), avec deux systèmes d'authentification qui coexistent :

1. **Auth applicative "API"** (`/api/auth/*`) — email + mot de passe, isolation multi-tenant par organisation, refresh token rotatif avec détection de vol.
2. **Auth SSO Shibboleth (mock)** (`/auth/shibboleth`) — simule un reverse-proxy `mod_shib` qui pousse un `REMOTE_USER`.

Il reste aussi un flux legacy (`/api/login_check`, utilisateurs en mémoire) conservé pour compatibilité mais non utilisé par l'UI de test.

---

## 🚀 Démarrer

```powershell
cd jwt-shibboleth-demo
php -S 127.0.0.1:8000 -t public
```

Interface de test : `http://127.0.0.1:8000/loginPage`

Après toute modification de `security.yaml` ou des entités : `php bin/console cache:clear` puis, si une entité a changé, `php bin/console doctrine:migrations:diff` + `doctrine:migrations:migrate`.

---

## 🧩 Modèle de données

Deux entités utilisateur distinctes, volontairement séparées :

- **`User`** (`src/Entity/User.php`) — utilisateur "métier", porteur de `organization_id` (le tenant). C'est la source de vérité pour l'organisation.
- **`ApiUser`** (`src/Entity/ApiUser.php`) — compte d'accès à l'API, lié à un `User` en `OneToOne`. Porte : email, mot de passe (argon2id via le hasher Symfony), rôles (`ROLE_API_RH` par défaut), statut (`pending|active|suspended|locked`), verrouillage après échecs de connexion.
  - `getOrganizationId()` délègue à `$this->user->organization_id` : l'organisation ne se définit jamais sur l'`ApiUser` lui-même, elle vient toujours du `User` lié.
- **`ApiRefreshToken`** (`src/Entity/ApiRefreshToken.php`) — un refresh token par ligne, on ne stocke **jamais** le token en clair : seul `hash('sha256', $token)` est persisté. Champs : `expiresAt`, `revoked`, `usedAt`.
- **`Training`** (`src/Entity/Training.php`) — exemple de ressource métier isolée par tenant (`organizationId`), créée pour démontrer l'isolation dans les endpoints `/api/v1/trainings`.

```
User (1) ──── (1) ApiUser (1) ──── (N) ApiRefreshToken
  │                  │
  organization_id    roles, status, mfaSecret(inutilisé), lockedUntil
```

### Créer un ApiUser

```powershell
php bin/console app:api-user:create alice@org1.com 1 --role=ROLE_API_RH --password=alice123
```

`1` est l'`id` d'un `User` existant. Le mot de passe est haché avec `UserPasswordHasherInterface` avant stockage.

---

## 🔐 Sécurité — comment les firewalls s'articulent

Config : `config/packages/security.yaml`. Les firewalls sont évalués **dans l'ordre**, le premier `pattern` qui matche gagne :

| Firewall | Pattern | Provider | Rôle |
|---|---|---|---|
| `dev` | `^/(_profiler\|_wdt\|assets\|build)/` | — | désactive la sécurité sur les assets |
| `login` | `^/api/login` | `users_in_memory` | legacy `json_login` sur `/api/login_check` |
| `shibboleth` | `^/auth/shibboleth` | `shibboleth_provider` | authentifie via `REMOTE_USER` |
| `api` | `^/api` | `api_users` | **toutes les autres routes `/api/*`**, JWT obligatoire |
| `main` | (tout le reste) | `users_in_memory` | pages web (`/loginPage`, `/mock-shibboleth`) |

Le provider `api_users` charge un `ApiUser` par email (`entity: { class: App\Entity\ApiUser, property: email }`). C'est ce provider que le firewall `api` utilise pour résoudre l'utilisateur à partir du claim `username` du JWT (qui contient l'email, voir plus bas).

---

## 🔑 Contenu du JWT

Émis par `lexik/jwt-authentication-bundle` (RS256, clés dans `config/jwt/`, TTL 3600s — voir `config/packages/lexik_jwt_authentication.yaml`).

Le payload standard de Lexik ne contient que `username` (= `getUserIdentifier()`), `roles`, `iat`, `exp`. Pour transporter l'organisation et l'email, `src/EventListener/JwtCreatedListener.php` écoute l'événement `lexik_jwt_authentication.on_jwt_created` et enrichit le payload **uniquement si l'utilisateur est un `ApiUser`** :

```json
{
  "iat": 1783429292,
  "exp": 1783432892,
  "roles": ["ROLE_API_RH"],
  "username": "alice@org1.com",
  "org_id": "org-123",
  "email": "alice@org1.com"
}
```

C'est ce `org_id` que les endpoints métier (ex. `/api/v1/trainings`) auraient pu lire directement dans le token ; en pratique le code actuel relit plutôt `$user->getOrganizationId()` via `#[CurrentUser]`, ce qui revalide la donnée en base plutôt que de faire confiance au claim.

---

## 📡 Endpoints

### Auth applicative (nouvelle, utilisée par l'UI de test)

| Méthode | Route | Auth requise | Description |
|---|---|---|---|
| POST | `/api/auth/login` | Aucune | `{ email, password }` → `{ access_token, refresh_token, expires_in, user }` |
| GET | `/api/auth/me` | Bearer JWT | Infos du compte courant (email, org, rôles, statut, dernière connexion) |
| POST | `/api/auth/refresh` | Aucune (le refresh token fait foi) | `{ refresh_token }` → nouvelle paire access/refresh (rotation) |

Détails dans `src/Controller/AuthController.php`.

**Login** (`login()`) :
1. Cherche l'`ApiUser` par email (`ApiUserRepository::findOneByEmail`).
2. Rejette si compte verrouillé (`isLocked()`, verrou 15 min après 5 échecs) ou non `active`.
3. Vérifie le mot de passe (`UserPasswordHasherInterface::isPasswordValid`) ; en cas d'échec, incrémente le compteur d'échecs et persiste, puis 401.
4. En cas de succès, remet le compteur à zéro, met à jour `lastLoginAt`, puis émet la paire de tokens.

**Refresh** (`refresh()`) — rotation avec détection de réutilisation :
1. Hash le token reçu (`sha256`) et cherche une ligne `ApiRefreshToken` valide (`revoked = false` et `expiresAt` future) via `findValidByTokenHash`.
2. Si la ligne trouvée a déjà un `usedAt` renseigné (`isReused()`), c'est qu'on a affaire à un **replay** : toute la famille de refresh tokens de cet `ApiUser` est révoquée (`revokeAllForApiUser`) et la requête est rejetée en 403. C'est la détection de vol de refresh token.
3. Sinon, marque le token comme utilisé (`markAsUsed()`) **sans le révoquer immédiatement** — c'est ce qui permet à l'étape 2 de détecter une deuxième utilisation du même token plus tard. Puis émet une nouvelle paire access/refresh.

⚠️ Piège corrigé pendant le développement : révoquer le token dès son utilisation empêche la détection de réutilisation (le second appel échoue simplement avec "token invalide" avant même d'atteindre la vérification `isReused()`). Le code actuel ne révoque le token qu'au moment où une réutilisation est détectée.

**Me** (`me()`) : lit simplement l'`ApiUser` injecté par `#[CurrentUser]` (résolu par le firewall `api` à partir du JWT) et retourne son profil.

### Formations (exemple d'isolation multi-tenant)

| Méthode | Route | Auth requise | Description |
|---|---|---|---|
| GET | `/api/v1/trainings` | Bearer JWT | Liste les formations de l'organisation de l'utilisateur courant |
| POST | `/api/v1/trainings` | Bearer JWT | `{ title }` → crée une formation rattachée à l'organisation courante |

`src/Controller/TrainingController.php` — chaque requête récupère `#[CurrentUser] ApiUser $user`, puis filtre/crée via `$user->getOrganizationId()`. Deux organisations ne voient jamais les formations l'une de l'autre, même en connaissant l'`id`.

### Legacy / démo

| Méthode | Route | Auth requise | Description |
|---|---|---|---|
| POST | `/api/login_check` | Aucune | Ancien flux `json_login`, comptes en mémoire (`alice`/`bob`). Le contrôleur n'est atteint qu'en cas d'échec — le succès est intercepté par le firewall `login`. |
| GET | `/api/public` | Aucune | Endpoint de démo public |
| GET | `/api/private` | JWT (provider `api_users` désormais) | Démo protégée — ne fonctionne plus avec un token issu de `/api/login_check` |
| GET | `/api/user-info` | JWT (idem) | Démo protégée |
| GET | `/auth/shibboleth` | `REMOTE_USER` posé par le firewall `shibboleth` | Émet un JWT pour l'utilisateur SSO |
| GET | `/mock-shibboleth?REMOTE_USER=alice` | Aucune | Simule le reverse-proxy Shibboleth, redirige vers `/auth/shibboleth` |

---

## 🧪 Tester via le navigateur

1. Aller sur `http://127.0.0.1:8000/loginPage`.
2. **Login** avec `alice@org1.com` / `alice123` (compte créé via `app:api-user:create`).
3. **Qui suis-je ?** → `GET /api/auth/me`.
4. **Refresh Token** → rotation ; puis **Simuler un vol** rejoue l'ancien refresh token et doit être rejeté en 403 (sécurité OK).
5. **Décoder le JWT** → affiche les claims (`org_id`, `roles`, `email`, `exp`…) décodés côté client.
6. **Formations** → lister / créer, scoping par organisation.
7. **Requête personnalisée** → tester n'importe quelle route avec le token courant.

## 🧪 Tester en ligne de commande

```powershell
# Login
$body = @{ email = "alice@org1.com"; password = "alice123" } | ConvertTo-Json
$res = Invoke-RestMethod -Uri http://127.0.0.1:8000/api/auth/login -Method Post -Body $body -ContentType "application/json"
$res.access_token

# Me
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/auth/me -Headers @{ Authorization = "Bearer $($res.access_token)" }

# Refresh
$refreshBody = @{ refresh_token = $res.refresh_token } | ConvertTo-Json
Invoke-RestMethod -Uri http://127.0.0.1:8000/api/auth/refresh -Method Post -Body $refreshBody -ContentType "application/json"
```

---

## 📁 Fichiers clés

```
src/Controller/
  ├── AuthController.php          # /api/auth/login, /me, /refresh + legacy /api/login_check
  ├── TrainingController.php      # /api/v1/trainings (list/create, isolation tenant)
  ├── ApiController.php           # /api/public, /api/private, /api/user-info (démo)
  ├── ShibbolethAuthController.php
  ├── MockShibbolethController.php
  └── TestingController.php       # sert l'UI de test (/loginPage)

src/Entity/
  ├── User.php                    # tenant (organization_id)
  ├── ApiUser.php                 # compte API lié à un User
  ├── ApiRefreshToken.php         # refresh tokens hashés, rotation
  └── Training.php                # ressource de démo isolée par tenant

src/Repository/
  ├── ApiUserRepository.php
  ├── ApiRefreshTokenRepository.php
  ├── UserRepository.php
  └── TrainingRepository.php

src/EventListener/
  └── JwtCreatedListener.php      # ajoute org_id/roles/email au payload JWT

src/Security/
  ├── ShibbolethUser.php
  ├── ShibbolethUserProvider.php
  └── ShibbolethAuthenticator.php

src/Command/
  └── CreateApiUserCommand.php    # app:api-user:create

config/jwt/            # private.pem / public.pem (RS256)
config/packages/
  ├── security.yaml                     # firewalls, providers, access_control
  └── lexik_jwt_authentication.yaml     # clés, TTL, extraction du Bearer

migrations/             # schéma : user, api_user, api_refresh_token, training
```

---

## 🔐 Séquence complète (auth applicative)

```
1. POST /api/auth/login { email, password }
   → vérifie ApiUser (verrouillage, statut, mot de passe)
   → JWTCreatedListener injecte org_id/roles/email dans le JWT
   → persiste un ApiRefreshToken (hash sha256 du refresh token brut)
   ↓
   { access_token, refresh_token, expires_in: 3600, user }

2. GET /api/v1/trainings + Authorization: Bearer <access_token>
   → firewall `api` valide le JWT, charge l'ApiUser via provider `api_users`
   → TrainingController filtre par $user->getOrganizationId()

3. Après expiration (ou avant) : POST /api/auth/refresh { refresh_token }
   → si le hash du token correspond à une ligne non révoquée et non expirée :
       - déjà utilisée (usedAt set) → vol détecté, révoque toute la famille, 403
       - sinon → marque comme utilisée, émet un nouveau couple access/refresh
```

---

## 🆘 Notes de dev

- Toujours `php bin/console cache:clear` après une modif de `security.yaml` (le firewall/provider est compilé dans le container).
- Toute nouvelle entité nécessite `doctrine:migrations:diff` puis `doctrine:migrations:migrate`.
- Les refresh tokens ne sont **jamais** stockés en clair — seule leur empreinte `sha256` l'est. En cas de fuite de la base, les tokens ne sont pas directement réutilisables sans casser le hash.
