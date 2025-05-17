"use client";

import { useEffect, useState } from "react";
import PowerButton from "@/components/PowerButton";
import { login, orderCoffee, getMachineUuid } from "@/lib/api";

export default function Home() {
    const [machineStatus, setMachineStatus] = useState(false);
    const [machineUuid, setMachineUuid] = useState<string | null>(null);
    const [isOrdering, setIsOrdering] = useState(false);
    const [selectedCoffeeType, setSelectedCoffeeType] = useState<string | null>(null);
    const [selectedIntensity, setSelectedIntensity] = useState<string | null>(null);
    const [selectedSugarLevel, setSelectedSugarLevel] = useState<string | null>(null);

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
            .then(() => console.log("✅ Connexion réussie"))
            .catch((error) => console.error("❌ Erreur de connexion :", error));
    }, []);

    useEffect(() => {
        login()
            .then(() => {
                console.log("✅ Connexion réussie");
                return getMachineUuid();
            })
            .then((uuid) => {
                console.log("🎯 UUID machine :", uuid);
                setMachineUuid(uuid);
            })
            .catch((error) => {
                console.error("❌ Erreur lors du chargement initial :", error);
            });
    }, []);


    function getDisplayMessage(): string {
        if (!machineStatus) {
            return "Au revoir et à demain ! La machine est éteinte 🌙";
        }
        if (isOrdering) {
            return "☕ Votre café est en cours de préparation. Merci de patienter…";
        }

        const coffeeLabel = coffeeTypes.find(c => c.value === selectedCoffeeType)?.label;
        const intensityLabel = intensityLevels.find(i => i.value === selectedIntensity)?.label;
        const sugarLabel = sugarLevels.find(s => s.value === selectedSugarLevel)?.label;

        const parts = [];
        if (coffeeLabel) parts.push(`☕ ${coffeeLabel}`);
        if (intensityLabel) parts.push(`🔥 ${intensityLabel}`);
        if (sugarLabel) parts.push(`🍬 ${sugarLabel}`);

        const base = parts.join(" - ");

        return parts.length === 3
            ? `🧾 ${base}. Prêt à commander !`
            : base || "✅ Bonjour ! Nous sommes prêts à prendre votre commande de café ☕";
    }

    async function handleOrder() {
        // 1. On vérifie que le client a bien sélectionné les 3 éléments
        if (!selectedCoffeeType || !selectedIntensity || !selectedSugarLevel) {
            alert("Veuillez choisir un type de café, une intensité et un niveau de sucre.");
            return;
        }

        // 2. On récupère le token JWT pour s’authentifier
        const token = localStorage.getItem("token");
        if (!token) {
            alert("Token manquant. Veuillez vous reconnecter.");
            return;
        }

        if (!machineUuid) {
            alert("La machine n'est pas encore prête. Veuillez patienter.");
            return;
        }


        try {
            // 3. On passe l’écran en “préparation en cours”
            setIsOrdering(true);

            // 4. On envoie la commande à l’API
            await orderCoffee(machineUuid, token, {
                type: selectedCoffeeType,
                intensity: selectedIntensity,
                sugar_level: selectedSugarLevel,
            });

            // 5. On attend 3 secondes (simulation du temps de préparation)
            setTimeout(() => {
                setIsOrdering(false);
                alert("✅ Votre café a été commandé avec succès !");
            }, 3000);
        } catch (error) {
            console.error("Erreur lors de la commande :", error);
            alert("❌ Échec de la commande. Veuillez réessayer.");
            setIsOrdering(false);
        }
    }


    return (
        <main className="bg-black min-h-screen text-white p-10">
            <h1 className="text-3xl font-bold text-center pt-10">
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
                        <div className="bg-black text-white border border-white rounded-md px-6 py-4 text-center w-full max-w-xl h-24 flex items-center justify-center text-lg">
                            {getDisplayMessage()}
                        </div>

                        {/* Boutons Commander / Annuler */}
                        <div className="flex flex-col gap-4">
                            <button
                                className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
                                onClick={handleOrder}
                            >
                                Commander
                            </button>
                            <button
                                className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                                onClick={() => {
                                    setSelectedCoffeeType(null);
                                    setSelectedIntensity(null);
                                    setSelectedSugarLevel(null);
                                }}
                            >
                                Annuler
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
                        />
                        <ChoiceGroup
                            title="Intensité :"
                            options={intensityLevels}
                            selected={selectedIntensity}
                            setSelected={setSelectedIntensity}
                        />
                        <ChoiceGroup
                            title="Sucre :"
                            options={sugarLevels}
                            selected={selectedSugarLevel}
                            setSelected={setSelectedSugarLevel}
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
                     }: {
    title: string;
    options: { label: string; value: string }[];
    selected: string | null;
    setSelected: (val: string) => void;
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
                                    }`}
                                    onClick={() => setSelected(opt.value)}
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
