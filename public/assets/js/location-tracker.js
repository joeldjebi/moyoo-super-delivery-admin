/**
 * Système de géolocalisation en temps réel avec Socket.IO
 * Pour l'application de livraison MOYOO
 */

class LocationTracker {
    constructor(config = {}) {
        this.config = {
            socketUrl: config.socketUrl || 'http://localhost:3001',
            apiUrl: config.apiUrl || '/api',
            token: config.token || null,
            updateInterval: config.updateInterval || 5000, // 5 secondes
            accuracyThreshold: config.accuracyThreshold || 100, // 100 mètres
            ...config
        };

        this.socket = null;
        this.watchId = null;
        this.isTracking = false;
        this.lastPosition = null;
        this.currentStatus = 'inactive';

        // Éléments DOM
        this.elements = {
            statusIndicator: document.getElementById('location-status'),
            trackingButton: document.getElementById('start-tracking'),
            statusSelect: document.getElementById('location-status-select'),
            mapContainer: document.getElementById('map-container'),
            locationHistory: document.getElementById('location-history'),
            connectionStatus: document.getElementById('connection-status')
        };

        this.init();
    }

    /**
     * Initialisation du tracker
     */
    init() {
        this.setupEventListeners();
        this.connectSocket();
        this.updateUI();
    }

    /**
     * Configuration des écouteurs d'événements
     */
    setupEventListeners() {
        // Bouton de démarrage/arrêt du suivi
        if (this.elements.trackingButton) {
            this.elements.trackingButton.addEventListener('click', () => {
                this.toggleTracking();
            });
        }

        // Sélecteur de statut
        if (this.elements.statusSelect) {
            this.elements.statusSelect.addEventListener('change', (e) => {
                this.updateStatus(e.target.value);
            });
        }

        // Gestion de la visibilité de la page
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseTracking();
            } else {
                this.resumeTracking();
            }
        });

        // Gestion de la fermeture de la page
        window.addEventListener('beforeunload', () => {
            this.stopTracking();
        });
    }

    /**
     * Connexion au serveur Socket.IO
     */
    connectSocket() {
        if (!this.config.token) {
            console.error('Token d\'authentification manquant');
            return;
        }

        this.socket = io(this.config.socketUrl, {
            auth: {
                token: this.config.token,
                userId: this.config.userId,
                userName: this.config.userName,
                userRole: this.config.userRole
            },
            transports: ['websocket', 'polling']
        });

        // Événements de connexion
        this.socket.on('connect', () => {
            console.log('🔗 Connecté au serveur Socket.IO');
            this.updateConnectionStatus('connected');
        });

        this.socket.on('disconnect', () => {
            console.log('🔌 Déconnecté du serveur Socket.IO');
            this.updateConnectionStatus('disconnected');
        });

        this.socket.on('connect_error', (error) => {
            console.error('❌ Erreur de connexion:', error);
            this.updateConnectionStatus('error');
        });

        // Événements de géolocalisation
        this.socket.on('location:updated', (data) => {
            console.log('✅ Position mise à jour:', data);
            this.updateLocationUI(data);
        });

        this.socket.on('location:error', (error) => {
            console.error('❌ Erreur position:', error);
            this.showError(error.message);
        });

        this.socket.on('location:status:changed', (data) => {
            console.log('📊 Statut changé:', data);
            this.currentStatus = data.status;
            this.updateUI();
        });

        this.socket.on('livreur:tracking:start', (data) => {
            console.log('🚀 Suivi démarré:', data);
            this.showSuccess('Suivi démarré');
        });

        this.socket.on('livreur:tracking:stop', (data) => {
            console.log('⏹️ Suivi arrêté:', data);
            this.showSuccess('Suivi arrêté');
        });
    }

    /**
     * Démarrer/Arrêter le suivi
     */
    toggleTracking() {
        if (this.isTracking) {
            this.stopTracking();
        } else {
            this.startTracking();
        }
    }

    /**
     * Démarrer le suivi
     */
    startTracking() {
        if (!navigator.geolocation) {
            this.showError('La géolocalisation n\'est pas supportée par ce navigateur');
            return;
        }

        if (!this.socket || !this.socket.connected) {
            this.showError('Connexion au serveur requise');
            return;
        }

        this.isTracking = true;
        this.currentStatus = 'active';

        // Options de géolocalisation
        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 5000
        };

        // Démarrer la surveillance
        this.watchId = navigator.geolocation.watchPosition(
            (position) => this.handlePositionUpdate(position),
            (error) => this.handlePositionError(error),
            options
        );

        // Notifier le serveur
        this.socket.emit('livreur:join');
        this.socket.emit('location:status:change', { status: 'active' });

        this.updateUI();
        this.showSuccess('Suivi démarré');
    }

    /**
     * Arrêter le suivi
     */
    stopTracking() {
        this.isTracking = false;
        this.currentStatus = 'inactive';

        if (this.watchId) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }

        // Notifier le serveur
        if (this.socket) {
            this.socket.emit('livreur:leave');
            this.socket.emit('location:status:change', { status: 'inactive' });
        }

        this.updateUI();
        this.showSuccess('Suivi arrêté');
    }

    /**
     * Mettre en pause le suivi
     */
    pauseTracking() {
        if (this.isTracking) {
            this.currentStatus = 'paused';
            this.socket.emit('location:status:change', { status: 'paused' });
            this.updateUI();
        }
    }

    /**
     * Reprendre le suivi
     */
    resumeTracking() {
        if (this.isTracking) {
            this.currentStatus = 'active';
            this.socket.emit('location:status:change', { status: 'active' });
            this.updateUI();
        }
    }

    /**
     * Gestionnaire de mise à jour de position
     */
    handlePositionUpdate(position) {
        const { latitude, longitude, accuracy, altitude, speed, heading } = position.coords;

        // Vérifier la précision
        if (accuracy > this.config.accuracyThreshold) {
            console.warn('Précision insuffisante:', accuracy);
            return;
        }

        // Vérifier si la position a changé significativement
        if (this.lastPosition) {
            const distance = this.calculateDistance(
                this.lastPosition.latitude,
                this.lastPosition.longitude,
                latitude,
                longitude
            );

            if (distance < 10) { // Moins de 10 mètres
                return;
            }
        }

        // Préparer les données
        const locationData = {
            latitude,
            longitude,
            accuracy,
            altitude: altitude || null,
            speed: speed || null,
            heading: heading || null,
            timestamp: new Date().toISOString(),
            status: this.currentStatus
        };

        // Envoyer au serveur
        this.socket.emit('location:update', locationData);

        // Sauvegarder la dernière position
        this.lastPosition = { latitude, longitude };

        // Mettre à jour l'interface
        this.updateLocationUI(locationData);
    }

    /**
     * Gestionnaire d'erreur de position
     */
    handlePositionError(error) {
        let message = 'Erreur de géolocalisation: ';

        switch (error.code) {
            case error.PERMISSION_DENIED:
                message += 'Permission refusée';
                break;
            case error.POSITION_UNAVAILABLE:
                message += 'Position indisponible';
                break;
            case error.TIMEOUT:
                message += 'Délai d\'attente dépassé';
                break;
            default:
                message += 'Erreur inconnue';
                break;
        }

        this.showError(message);
    }

    /**
     * Mettre à jour le statut
     */
    updateStatus(status) {
        this.currentStatus = status;
        this.socket.emit('location:status:change', { status });
        this.updateUI();
    }

    /**
     * Mettre à jour l'interface utilisateur
     */
    updateUI() {
        // Indicateur de statut
        if (this.elements.statusIndicator) {
            this.elements.statusIndicator.className = `status-indicator ${this.currentStatus}`;
            this.elements.statusIndicator.textContent = this.getStatusText(this.currentStatus);
        }

        // Bouton de suivi
        if (this.elements.trackingButton) {
            this.elements.trackingButton.textContent = this.isTracking ? 'Arrêter le suivi' : 'Démarrer le suivi';
            this.elements.trackingButton.className = this.isTracking ? 'btn btn-danger' : 'btn btn-success';
        }

        // Sélecteur de statut
        if (this.elements.statusSelect) {
            this.elements.statusSelect.value = this.currentStatus;
        }
    }

    /**
     * Mettre à jour l'interface de position
     */
    updateLocationUI(data) {
        // Afficher les coordonnées
        const coordsElement = document.getElementById('current-coordinates');
        if (coordsElement) {
            coordsElement.innerHTML = `
                <strong>Latitude:</strong> ${data.latitude.toFixed(6)}<br>
                <strong>Longitude:</strong> ${data.longitude.toFixed(6)}<br>
                <strong>Précision:</strong> ${data.accuracy ? data.accuracy.toFixed(2) + 'm' : 'N/A'}<br>
                <strong>Vitesse:</strong> ${data.speed ? (data.speed * 3.6).toFixed(2) + ' km/h' : 'N/A'}
            `;
        }

        // Ajouter à l'historique
        this.addToHistory(data);
    }

    /**
     * Ajouter à l'historique
     */
    addToHistory(data) {
        if (!this.elements.locationHistory) return;

        const historyItem = document.createElement('div');
        historyItem.className = 'history-item';
        historyItem.innerHTML = `
            <div class="history-time">${new Date(data.timestamp).toLocaleTimeString()}</div>
            <div class="history-coords">
                ${data.latitude.toFixed(6)}, ${data.longitude.toFixed(6)}
            </div>
            <div class="history-accuracy">Précision: ${data.accuracy ? data.accuracy.toFixed(2) + 'm' : 'N/A'}</div>
        `;

        // Ajouter au début de la liste
        this.elements.locationHistory.insertBefore(historyItem, this.elements.locationHistory.firstChild);

        // Limiter à 50 éléments
        const items = this.elements.locationHistory.querySelectorAll('.history-item');
        if (items.length > 50) {
            items[items.length - 1].remove();
        }
    }

    /**
     * Mettre à jour le statut de connexion
     */
    updateConnectionStatus(status) {
        if (!this.elements.connectionStatus) return;

        const statusMap = {
            'connected': { text: 'Connecté', class: 'connected' },
            'disconnected': { text: 'Déconnecté', class: 'disconnected' },
            'error': { text: 'Erreur', class: 'error' }
        };

        const statusInfo = statusMap[status] || { text: 'Inconnu', class: 'unknown' };
        this.elements.connectionStatus.textContent = statusInfo.text;
        this.elements.connectionStatus.className = `connection-status ${statusInfo.class}`;
    }

    /**
     * Calculer la distance entre deux points
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Rayon de la Terre en mètres
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;

        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    /**
     * Obtenir le texte du statut
     */
    getStatusText(status) {
        const statusMap = {
            'active': 'Actif',
            'inactive': 'Inactif',
            'paused': 'En pause'
        };
        return statusMap[status] || 'Inconnu';
    }

    /**
     * Afficher un message de succès
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    /**
     * Afficher un message d'erreur
     */
    showError(message) {
        this.showNotification(message, 'error');
    }

    /**
     * Afficher une notification
     */
    showNotification(message, type = 'info') {
        // Créer l'élément de notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;

        // Ajouter au DOM
        document.body.appendChild(notification);

        // Supprimer après 3 secondes
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    /**
     * Obtenir l'historique des positions via API
     */
    async getLocationHistory(params = {}) {
        try {
            const response = await fetch(`${this.config.apiUrl}/livreur/location/history?${new URLSearchParams(params)}`, {
                headers: {
                    'Authorization': `Bearer ${this.config.token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erreur récupération historique:', error);
            return null;
        }
    }

    /**
     * Mettre à jour la position via API
     */
    async updateLocationViaAPI(locationData) {
        try {
            const response = await fetch(`${this.config.apiUrl}/livreur/location/update`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.config.token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(locationData)
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Erreur mise à jour position:', error);
            return null;
        }
    }
}

// Export pour utilisation globale
window.LocationTracker = LocationTracker;
