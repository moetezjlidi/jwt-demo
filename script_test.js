const { email, password } = require("./config");

const BASE_URL = "https://jwt-demo-production.up.railway.app";

async function login() {
    const response = await fetch(`${BASE_URL}/api/auth/login`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(JSON.stringify(data));
    }

    return data.access_token;
}


async function getTrainings(token) {
    const response = await fetch(`${BASE_URL}/api/v1/trainings`, {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${token}`
        }
    });

    const data = await response.json();

    if (!response.ok) {
        throw new Error(JSON.stringify(data));
    }

    return data;
}


async function main() {
    try {
        const token = await login();
        console.log(token);
        console.log("API JWT received");

        const trainings = await getTrainings(token);
        console.log(
            "--------------------------------"
        );
        console.log(
            "Trainings for your organization:"
        );

        console.log(JSON.stringify(trainings, null, 2));

    } catch (error) {
        console.error(error.message);
    }
}

main();