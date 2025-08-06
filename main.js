

const root = Vue.createApp({
    data() {
        return {
            currentPage: "Home",
            orari: [
                { id: 1, giorno: "Lunedì", Mattino: "08:30/12:30", pomeriggio: "chiuso" },
                { id: 2, giorno: "Martedì", Mattino: "chiuso", pomeriggio: "chiuso" },
                { id: 3, giorno: "Mercoledì", Mattino: "chiuso", pomeriggio: "14:45/17:00" },
                { id: 4, giorno: "Giovedì", Mattino: "08:30/12:30", pomeriggio: "chiuso" },
                { id: 5, giorno: "Venerdì", Mattino: "08:30/12:30 [su appuntamento]", pomeriggio: "" }
            ],
            eventi:[
                { 
                  id:1,
                  titolo:'GITA A ROMA',
                  descrizione:'Partenza da Voghera Piazza Duomo e Arrivo a Roma dove visiteremo SanPietro e la Cappella Sistina la gita avra una durata di 3 giorni con partenza da voghera il 7/8/2025 e ritorno il 10 in tardaata prezzo di 350€ a persona ',
                  button:''
                  
                 
                },
             
            ]
        }
    },
    methods: {
        showPage(page) {
            this.currentPage = page;
        },
                condividiFacebook(evento) {
            const titolo = evento.titolo;
            const descrizione = evento.descrizione;
            
            // Testo completo dell'evento da condividere
            const testoEvento = `🎉 ${titolo}

${descrizione}

📍 MCL Voghera - Circolo Giovanni XXIII
📞 Per info: 0383-42980`;
            
            // Copia il testo negli appunti e avvisa l'utente
            if (navigator.clipboard) {
                navigator.clipboard.writeText(testoEvento).then(() => {
                    alert('📋 Testo dell\'evento copiato negli appunti!\n\nOra puoi incollarlo direttamente in un post Facebook.');
                });
            } else {
                // Fallback per browser più vecchi
                const textArea = document.createElement('textarea');
                textArea.value = testoEvento;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('📋 Testo dell\'evento copiato negli appunti!\n\nOra puoi incollarlo direttamente in un post Facebook.');
            }
        }
    }
});

root.mount('#root');
