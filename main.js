

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
            const url = 'https://sebaminotti.github.io/mclvoghera/'; // URL della tua webapp
            const titolo = evento.titolo;
            const descrizione = evento.descrizione.length > 200 ? 
                evento.descrizione.substring(0, 200) + '...' : 
                evento.descrizione;
            
            const testoCondivisione = `${titolo}\n\n${descrizione}\n\nMCL Voghera - Circolo Giovanni XXIII`;
            
            // URL di condivisione Facebook
            const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(testoCondivisione)}`;
            
            // Apre il popup di condivisione
            window.open(facebookUrl, 'facebook-share', 'width=600,height=400,scrollbars=yes,resizable=yes');
        }
    }
});

root.mount('#root');
