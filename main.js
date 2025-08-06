const { createApp } = Vue;

createApp({
    data() {
        return {
            currentPage: "Home",
            // Login e gestione eventi
            isLoggedIn: false,
            user: null,
            showLoginForm: false,
            showAddEventForm: false,
            loginForm: {
                username: '',
                password: ''
            },
            addEventForm: {
                titolo: '',
                descrizione: '',
                data_evento: '',
                prezzo: ''
            },
            loginMessage: '',
            eventMessage: '',
            // Dati statici
            orari: [
                { id: 1, giorno: "Lunedì", Mattino: "08:30/12:30", pomeriggio: "chiuso" },
                { id: 2, giorno: "Martedì", Mattino: "chiuso", pomeriggio: "chiuso" },
                { id: 3, giorno: "Mercoledì", Mattino: "chiuso", pomeriggio: "14:45/17:00" },
                { id: 4, giorno: "Giovedì", Mattino: "08:30/12:30", pomeriggio: "chiuso" },
                { id: 5, giorno: "Venerdì", Mattino: "08:30/12:30 [su appuntamento]", pomeriggio: "" }
            ],
            eventi: []
        }
    },
    async mounted() {
        console.log('App montata, inizializzo...');
        await this.checkAuth();
        await this.loadEventi();
    },
    methods: {
        showPage(page) {
            this.currentPage = page;
            if (page === 'Bacheca') {
                this.loadEventi();
            }
        },
        
        // Metodi per autenticazione
        async checkAuth() {
            try {
                console.log('Controllo autenticazione...');
                const response = await fetch('db.php?action=check_auth');
                const data = await response.json();
                console.log('Risultato check_auth:', data);
                this.isLoggedIn = data.logged_in || false;
                this.user = data.user || null;
            } catch (error) {
                console.error('Errore nel controllo autenticazione:', error);
                this.isLoggedIn = false;
                this.user = null;
            }
        },
        
        async login() {
            console.log('Tentativo di login con:', this.loginForm);
            
            if (!this.loginForm.username || !this.loginForm.password) {
                this.loginMessage = 'Inserisci username e password';
                setTimeout(() => this.loginMessage = '', 3000);
                return;
            }
            
            try {
                const response = await fetch('db.php?action=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.loginForm)
                });
                
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Risposta login:', data);
                
                if (data.success) {
                    this.isLoggedIn = true;
                    this.user = data.user;
                    this.showLoginForm = false;
                    this.loginMessage = data.message;
                    this.loginForm = { username: '', password: '' };
                    setTimeout(() => this.loginMessage = '', 3000);
                } else {
                    this.loginMessage = data.message || 'Errore nel login';
                    setTimeout(() => this.loginMessage = '', 5000);
                }
            } catch (error) {
                console.error('Errore nel login:', error);
                this.loginMessage = 'Errore di connessione al server';
                setTimeout(() => this.loginMessage = '', 5000);
            }
        },
        
        async logout() {
            try {
                console.log('Logout...');
                const response = await fetch('db.php?action=logout');
                const data = await response.json();
                console.log('Risultato logout:', data);
                
                this.isLoggedIn = false;
                this.user = null;
                this.showAddEventForm = false;
                this.showLoginForm = false;
            } catch (error) {
                console.error('Errore nel logout:', error);
            }
        },
        
        // Metodi per gestione eventi
        async loadEventi() {
            try {
                console.log('Caricamento eventi...');
                const response = await fetch('db.php?action=get_eventi');
                const data = await response.json();
                console.log('Eventi caricati:', data);
                this.eventi = data || [];
            } catch (error) {
                console.error('Errore nel caricamento eventi:', error);
                this.eventi = [];
            }
        },
        
        async addEvento() {
            if (!this.addEventForm.titolo || !this.addEventForm.descrizione) {
                this.eventMessage = 'Titolo e descrizione sono obbligatori';
                setTimeout(() => this.eventMessage = '', 5000);
                return;
            }
            
            try {
                console.log('Aggiunta evento:', this.addEventForm);
                const response = await fetch('db.php?action=add_evento', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.addEventForm)
                });
                
                const data = await response.json();
                console.log('Risultato aggiunta evento:', data);
                
                if (data.success) {
                    this.eventMessage = data.message;
                    this.addEventForm = { titolo: '', descrizione: '', data_evento: '', prezzo: '' };
                    this.showAddEventForm = false;
                    await this.loadEventi();
                    setTimeout(() => this.eventMessage = '', 3000);
                } else {
                    this.eventMessage = data.message;
                    setTimeout(() => this.eventMessage = '', 5000);
                }
            } catch (error) {
                console.error('Errore nell\'aggiungere evento:', error);
                this.eventMessage = 'Errore di connessione al server';
                setTimeout(() => this.eventMessage = '', 5000);
            }
        },
        
        async deleteEvento(id) {
            if (!confirm('Sei sicuro di voler eliminare questo evento?')) {
                return;
            }
            
            try {
                console.log('Eliminazione evento:', id);
                const response = await fetch('db.php?action=delete_evento', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });
                
                const data = await response.json();
                console.log('Risultato eliminazione evento:', data);
                
                if (data.success) {
                    this.eventMessage = data.message;
                    await this.loadEventi();
                    setTimeout(() => this.eventMessage = '', 3000);
                } else {
                    this.eventMessage = data.message;
                    setTimeout(() => this.eventMessage = '', 5000);
                }
            } catch (error) {
                console.error('Errore nell\'eliminare evento:', error);
                this.eventMessage = 'Errore di connessione al server';
                setTimeout(() => this.eventMessage = '', 5000);
            }
        },
        
        // Utility methods
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('it-IT', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },
        
        formatPrice(price) {
            if (!price || price == 0) return '';
            return `€ ${parseFloat(price).toFixed(2)}`;
        }
    }
}).mount('#root');
