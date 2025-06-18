"use client";

import React from "react";
import { Power } from "lucide-react";
import { login } from "../lib/api";

type Props = {
    machineStatus: boolean;
    setMachineStatus: (value: boolean) => void;
};

export default function PowerButton({ machineStatus, setMachineStatus }: Props) {
    const handleClick = async () => {
        try {
            let token = localStorage.getItem("token");

            if (!token) {
                token = await login();
            }

            setMachineStatus(!machineStatus);
        } catch (error) {
            console.error("Erreur d'authentification :", error);
        }
    };

    return (
        <button
            onClick={handleClick}
            className="p-4 rounded-full shadow-lg bg-white text-black"
        >
            <Power size={32} />
        </button>
    );
}
