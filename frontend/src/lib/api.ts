export async function login(): Promise<string> {
    const response = await fetch('http://localhost:8080/api/login_check', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            username: 'admin',
            password: 'admin',
        }),
    });

    if (!response.ok) {
        throw new Error('Échec de la connexion');
    }

    const data = await response.json();
    const token = data.token;

    localStorage.setItem('token', token);

    return token;
}

export async function getMachineUuid(): Promise<string> {
    const token = localStorage.getItem("token");

    const response = await fetch("http://localhost:8080/api/machine", {
        headers: {
            "Authorization": `Bearer ${token}`
        }
    });

    if (!response.ok) {
        throw new Error("Impossible de récupérer l'UUID de la machine");
    }

    const data = await response.json();
    return data.uuid;
}



export async function orderCoffee(machineUuid: string, token: string, order: {
    type: string;
    intensity: string;
    sugar_level: string;
}): Promise<any> {
    const response = await fetch(`http://localhost:8080/api/machines/${machineUuid}/orders`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(order)
    });

    if (!response.ok) {
        throw new Error('Échec de la commande');
    }

    return await response.json();
}

export async function cancelOrder(machineUuid: string, token: string): Promise<string | null> {
    try {
        const response = await fetch(
            `http://localhost:8080/api/machines/${machineUuid}/orders/last`,
            {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            }
        );

        const data = await response.json();

        if (!response.ok) {
            return data.message || 'Erreur lors de l\'annulation';
        }

        return null;
    } catch (error) {
        console.error('Erreur lors de l\'annulation:', error);
        return 'Erreur de connexion au serveur';
    }
}