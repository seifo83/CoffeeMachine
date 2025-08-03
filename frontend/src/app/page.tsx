"use client";

import React, { useEffect, useState } from "react";
import PowerButton from "@/components/PowerButton";
import { login, orderCoffee, getMachineUuid, cancelOrder } from "@/lib/api";

export default function Home() {
    const [machineStatus, setMachineStatus] = useState(false);
    const [machineUuid, setMachineUuid] = useState<string | null>(null);
    const [statusMessage, setStatusMessage] = useState<string | null>(null);
    const [isOrdering, setIsOrdering] = useState(false);
    const [eventSource, setEventSource] = useState<EventSource | null>(null);
    const [selectedCoffeeType, setSelectedCoffeeType] = useState<string | null>(null);
    const [selectedIntensity, setSelectedIntensity] = useState<string | null>(null);
    const [selectedSugarLevel, setSelectedSugarLevel] = useState<string | null>(null);
    const [progressPercentage, setProgressPercentage] = useState(0);
    const [statusHistory, setStatusHistory] = useState<{ message: string, stepIndex: number }[]>([]);
    const [isCancellable, setIsCancellable] = useState(true);


    const coffeeTypes = [
        { label: "Espresso", value: "espresso" },
        { label: "Cappuccino", value: "cappuccino" },
        { label: "Latte", value: "latte" },
        { label: "Americano", value: "americano" },
        { label: "Mocha", value: "mocha" },
    ];

    const intensityLevels = [
        { label: "Léger", value: "low" },
        { label: "Moyen", value: "medium" },
        { label: "Fort", value: "hard" },
    ];

    const sugarLevels = [
        { label: "Sans", value: "0" },
        { label: "1 Cube", value: "1_dose" },
        { label: "2 Cubes", value: "2_doses" },
        { label: "3 Cubes", value: "3_doses" },
    ];

    useEffect(() => {
        login()
            .then(() => console.log("Connexion réussie"))
            .catch((error) => console.error("Erreur de connexion :", error));
    }, []);

    useEffect(() => {
        login()
            .then(() => {
                return getMachineUuid();
            })
            .then((uuid) => {
                setMachineUuid(uuid);
            })
            .catch((error) => {
                console.error("Erreur lors du chargement initial :", error);
            });
    }, []);

    function getDisplayMessage(): string {
        if (statusMessage) return statusMessage;
        if (!machineStatus) {
            return "Au revoir et à demain ! La machine est éteinte";
        }
        if (isOrdering) {
            return "Votre café est en cours de préparation. Merci de patienter…";
        }

        const coffeeLabel = coffeeTypes.find(c => c.value === selectedCoffeeType)?.label;
        const intensityLabel = intensityLevels.find(i => i.value === selectedIntensity)?.label;
        const sugarLabel = sugarLevels.find(s => s.value === selectedSugarLevel)?.label;

        const parts = [];
        if (coffeeLabel) parts.push(`☕ ${coffeeLabel}`);
        if (intensityLabel) parts.push(`🔥 ${intensityLabel}`);
        if (sugarLabel) parts.push(`🍬 ${sugarLabel }`);

        const base = parts.join(" - ");

        return parts.length === 3
            ? `🧾 ${base}. Prêt à commander !`
            : base || "Bonjour ! Nous sommes prêts à prendre votre commande de café";
    }

    async function handleOrder() {
        if (!selectedCoffeeType || !selectedIntensity || !selectedSugarLevel) {
            setStatusMessage("Veuillez faire tous les choix avant de commander.");

            setTimeout(() => {
                setStatusMessage(null);
                setSelectedCoffeeType(null);
                setSelectedIntensity(null);
                setSelectedSugarLevel(null);
            }, 3000);

            return;
        }

        const token = localStorage.getItem("token");
        if (!token) {
            console.error("Token manquant. Veuillez vous reconnecter.");
            return;
        }

        if (!machineUuid) {
            setStatusMessage("La machine n'est pas encore prête. Veuillez patienter.");
            return;
        }

        try {
            setIsOrdering(true);
            setProgressPercentage(0);
            setStatusHistory([]);

            if (eventSource) {
                eventSource.close();
            }

            const response = await orderCoffee(machineUuid, token, {
                type: selectedCoffeeType,
                intensity: selectedIntensity,
                sugar_level: selectedSugarLevel,
            });

            const newEventSource = await listenToOrderUpdates(
                response.uuid,
                setStatusMessage,
                setProgressPercentage,
                setStatusHistory,
                setIsOrdering,
                setSelectedCoffeeType,
                setSelectedIntensity,
                setSelectedSugarLevel,
                setEventSource
            );

            if (newEventSource) {
                setEventSource(newEventSource);
            }
        } catch (error) {
            console.error("Erreur lors de la commande :", error);
            setStatusMessage("Problème technique. Veuillez contacter le service.");
            setIsOrdering(false);
        }
    }

    async function handleCancelOrder() {
        if (!machineUuid) {
            console.error("Impossible d'annuler : Machine non identifiée");
            return;
        }

        try {
            const token = localStorage.getItem("token");
            if (!token) {
                console.error("Token manquant. Veuillez vous reconnecter.");
                return;
            }

            await cancelOrder(machineUuid, token);

            if (eventSource) {
                eventSource.close();
                setEventSource(null);
            }

            setIsOrdering(false);
            setStatusMessage("Commande annulée");
            setProgressPercentage(0);
            setStatusHistory([]);
            setSelectedCoffeeType(null);
            setSelectedIntensity(null);
            setSelectedSugarLevel(null);

        } catch (error: any) {
            if (error instanceof Error) {
                console.error(`Impossible d'annuler la commande: ${error.message}`);
            } else {
                console.error("Impossible d'annuler la commande");
            }
        }
    }

    async function handleCancel() {
        if (isOrdering) {
            try {
                const token = localStorage.getItem("token");
                if (!token) {
                    setStatusMessage("Token manquant. Veuillez vous reconnecter.");
                    return;
                }

                if (!machineUuid) {
                    setStatusMessage("Machine non identifiée.");
                    return;
                }

                const errorMessage = await cancelOrder(machineUuid, token);

                if (errorMessage) {
                    setStatusMessage(errorMessage);
                    return;
                }

                if (eventSource) {
                    eventSource.close();
                    setEventSource(null);
                }

                setIsOrdering(false);
                setStatusMessage("Commande annulée avec succès");
                setProgressPercentage(0);
                setStatusHistory([]);
                setSelectedCoffeeType(null);
                setSelectedIntensity(null);
                setSelectedSugarLevel(null);

                setTimeout(() => {
                    setStatusMessage(null);
                }, 3000);
            } catch (error) {
                console.error("Erreur d'annulation:", error);
                setStatusMessage("Erreur lors de l'annulation");
            }
        }
        // Si aucune commande n'est en cours, on réinitialise juste les sélections
        else {
            setSelectedCoffeeType(null);
            setSelectedIntensity(null);
            setSelectedSugarLevel(null);
        }
    }

    return (
        <main className="bg-black min-h-screen text-white p-10">
            <h1 className="text-3xl font-bold text-center">
                Machine à Café by Coffreo
            </h1>

            <div className="mt-16 flex justify-center">
                <div className="bg-gray-900 p-10 rounded-lg shadow-lg flex flex-col gap-10 items-center w-full max-w-4xl">

                    {/* Haut : Bouton Power + Écran + Commande */}
                    <div className="w-full flex justify-between items-start">
                        <PowerButton
                            machineStatus={machineStatus}
                            setMachineStatus={setMachineStatus}
                        />

                        {/* Écran d'affichage dynamique */}
                        <div className="bg-black text-white border border-white rounded-md px-6 py-4 text-center w-full max-w-xl flex flex-col items-center justify-start"
                             style={{minHeight: "120px"}}
                        >
                            <div
                                className="bg-black text-white border border-white rounded-md px-6 py-4 text-center w-full max-w-xl h-24 flex items-center justify-center text-lg">
                                {getDisplayMessage()}
                            </div>

                            {isOrdering && (
                                <div className="w-full bg-gray-800 rounded-full h-2 mt-2 mb-3">
                                    <div
                                        className="bg-green-500 h-2 rounded-full transition-all duration-500"
                                        style={{ width: `${progressPercentage}%` }}
                                    ></div>
                                </div>
                            )}

                            {isOrdering && statusHistory.length > 0 && (
                                <div className="w-full mt-2 text-sm text-left max-h-24 overflow-y-auto">
                                    {statusHistory
                                        .sort((a, b) => a.stepIndex - b.stepIndex) // tri explicite (par sécurité)
                                        .map((entry, index) => (
                                            <div key={entry.stepIndex} className="mb-1 flex items-start">
                                                <span className="text-gray-400 mr-2">
                                                    {index === statusHistory.length - 1 ? '→' : '✓'}
                                                </span>
                                                <span className={index === statusHistory.length - 1 ? 'font-bold' : 'text-gray-400'}>
                                                    {entry.message}
                                                </span>
                                            </div>
                                        ))}
                                </div>
                            )}
                        </div>

                        {/* Boutons Commander / Annuler */}
                        <div className="flex flex-col gap-4">
                            <button
                                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
                                onClick={handleOrder}
                                disabled={isOrdering || !machineStatus}
                            >
                                Commander
                            </button>
                            <button
                                className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                                onClick={handleCancel}
                            >
                                {isOrdering ? "Annuler café" : "Annuler"}
                            </button>
                        </div>
                    </div>

                    {/* Bloc central de sélection */}
                    <div className="flex flex-col gap-6 items-center w-full">
                    <ChoiceGroup
                            title="Café :"
                            options={coffeeTypes}
                            selected={selectedCoffeeType}
                            setSelected={setSelectedCoffeeType}
                            disabled={isOrdering || !machineStatus}
                        />
                        <ChoiceGroup
                            title="Intensité :"
                            options={intensityLevels}
                            selected={selectedIntensity}
                            setSelected={setSelectedIntensity}
                            disabled={isOrdering || !machineStatus}
                        />
                        <ChoiceGroup
                            title="Sucre :"
                            options={sugarLevels}
                            selected={selectedSugarLevel}
                            setSelected={setSelectedSugarLevel}
                            disabled={isOrdering || !machineStatus}
                        />
                    </div>
                </div>
            </div>
        </main>
    );
}

function ChoiceGroup({
                         title,
                         options,
                         selected,
                         setSelected,
                         disabled = false,
                     }: {
    title: string;
    options: { label: string; value: string }[];
    selected: string | null;
    setSelected: (val: string) => void;
    disabled?: boolean;
}) {
    const paddedOptions = [...options];
    while (paddedOptions.length % 3 !== 0) {
        paddedOptions.push({ label: "", value: `empty-${paddedOptions.length}` });
    }

    return (
        <div className="flex items-start gap-4 w-full">
            <span className="font-semibold w-[100px] text-right pt-3 text-lg">{title}</span>

            <div className="border border-white rounded-lg p-4 bg-gray-800 w-full max-w-xl">
                <div className="grid grid-cols-3 gap-4">
                    {paddedOptions.map((opt) => (
                        <div key={opt.value}>
                            {opt.label ? (
                                <button
                                    className={`px-4 py-2 rounded border text-sm w-full ${
                                        selected === opt.value
                                            ? "bg-yellow-400 text-black font-bold"
                                            : "bg-white text-black hover:bg-yellow-100"
                                    } ${disabled ? "opacity-50 cursor-not-allowed" : ""}`}
                                    onClick={() => !disabled && setSelected(opt.value)}
                                    disabled={disabled}
                                >
                                    {opt.label}
                                </button>
                            ) : (
                                <div className="invisible">&nbsp;</div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}

async function listenToOrderUpdates(
    orderUuid: string,
    setStatusMessage: (msg: string) => void,
    setProgressPercentage: (progress: number) => void,
    setStatusHistory: React.Dispatch<React.SetStateAction<{ message: string, stepIndex: number }[]>>,
    setIsOrdering: (isOrdering: boolean) => void,
    setSelectedCoffeeType: (type: string | null) => void,
    setSelectedIntensity: (intensity: string | null) => void,
    setSelectedSugarLevel: (sugar: string | null) => void,
    setEventSource: (es: EventSource | null) => void
) {
    try {
        const url = new URL("http://localhost:3002/.well-known/mercure");
        url.searchParams.append("topic", `orders/${orderUuid}`);

        const eventSource = new EventSource(url.toString());

        eventSource.onopen = () => {
            setStatusMessage("En attente de mise à jour de votre commande...");
        };

        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                const status = data.status;
                const stepIndex = data.stepIndex ?? 999;
                let message = "";
                let progress = 0;

                switch (status) {
                    case "received":
                        message = "Votre commande a été reçue par la machine.";
                        progress = 10;
                        break;
                    case "grinding":
                        message = "Mouture des grains de café en cours...";
                        progress = 30;
                        break;
                    case "heating":
                        message = "Chauffe de l'eau en cours...";
                        progress = 50;
                        break;
                    case "brewing":
                        message = "Infusion en cours...";
                        progress = 70;
                        break;
                    case "finalizing":
                        message = "Finalisation de votre café...";
                        progress = 90;
                        break;
                    case "ready":
                    case "COMPLETED":
                        message = "Votre café est prêt. Bonne dégustation !";
                        progress = 100;

                        setTimeout(() => {
                            eventSource.close();
                            setEventSource(null);
                            setIsOrdering(false);
                            setStatusMessage(null);
                            setProgressPercentage(0);
                            setStatusHistory([]);
                            setSelectedCoffeeType(null);
                            setSelectedIntensity(null);
                            setSelectedSugarLevel(null);
                        }, 5000);
                        break;
                    default:
                        message = "Mise à jour de la commande : " + status;
                        progress = 20;
                }

                if (data.description) {
                    message = data.description;
                }

                setStatusMessage(message);
                setProgressPercentage(progress);

                setStatusHistory(prev => {
                    const newEntry = { message, stepIndex };
                    const combined = [...prev, newEntry];

                    const unique = combined.filter((entry, idx, self) =>
                        idx === self.findIndex(e => e.stepIndex === entry.stepIndex)
                    );

                    return unique.sort((a, b) => a.stepIndex - b.stepIndex);
                });

            } catch (error) {
                console.error("Erreur de parsing Mercure:", error);
            }
        };

        eventSource.onerror = (error) => {
            console.error("Erreur de connexion Mercure :", error);
        };

        return eventSource;
    } catch (error) {
        console.error("Erreur lors de la configuration de Mercure :", error);
        setStatusMessage("Problème de connexion au serveur d'événements");
        return null;
    }
}
