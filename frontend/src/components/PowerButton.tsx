"use client";

import React from "react";
import { Power } from "lucide-react";

type Props = {
    machineStatus: boolean;
    setMachineStatus: (value: boolean) => void;
};

export default function PowerButton({ machineStatus, setMachineStatus }: Props) {
    return (
        <button
            onClick={() => setMachineStatus(!machineStatus)}
            className={`p-4 rounded-full shadow-lg 
                ${machineStatus ? "dark:bg-white/[.70] text-black" : "bg-white text-black"}
      `}
        >
            <Power size={32} />
        </button>
    );
}
