/**
 * Moniteur de géolocalisation pour les administrateurs
 * Interface de suivi en temps réel des livreurs
 */

class AdminLocationMonitor {
    constructor(config = {}) {
        this.config = {
            socketUrl: config.socketUrl || 'http://localhost:3001',
            apiUrl: config.apiUrl || '/api',
            token: config.token || null,
            updateInterval: config.updateInterval || 10000, // 10 secondes
            ...config
        };

        this.socket = null;
        this.livreurs = new Map();
        this.map = null;
        this.markers = new Map();

        // Éléments DOM
        this.elements = {
            livreursList: document.getElementById('livreurs-list'),
            mapContainer: document.getElementById('admin-map'),
            connectionStatus: document.getElementById('admin-connection-status'),
            statsContainer: document.getElementById('stats-container')
        };

        this.init();
    }

    /**
     * Initialisation du moniteur
     */
    init() {
        this.setupEventListeners();
        this.connectSocket();
        this.initMap();
        this.loadLivreursData();
    }

    /**
     * Configuration des écouteurs d'événements
     */
    setupEventListeners() {
        // Rafraîchissement automatique
        setInterval(() => {
            this.loadLivreursData();
        }, this.config.updateInterval);

        // Gestion de la fermeture de la page
        window.addEventListener('beforeunload', () => {
            this.disconnect();
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
            console.log('🔗 Admin connecté au serveur Socket.IO');
            this.updateConnectionStatus('connected');
            this.socket.emit('admin:join');
        });

        this.socket.on('disconnect', () => {
            console.log('🔌 Admin déconnecté du serveur Socket.IO');
            this.updateConnectionStatus('disconnected');
        });

        this.socket.on('connect_error', (error) => {
            console.error('❌ Erreur de connexion admin:', error);
            this.updateConnectionStatus('error');
        });

        // Événements de géolocalisation
        this.socket.on('admin:livreur:location', (data) => {
            console.log('📍 Position livreur reçue:', data);
            this.updateLivreurLocation(data);
        });

        this.socket.on('livreur:online', (data) => {
            console.log('🟢 Livreur en ligne:', data);
            this.addLivreur(data);
        });

        this.socket.on('livreur:offline', (data) => {
            console.log('🔴 Livreur hors ligne:', data);
            this.removeLivreur(data.livreur_id);
        });

        this.socket.on('livreur:status:changed', (data) => {
            console.log('📊 Statut livreur changé:', data);
            this.updateLivreurStatus(data);
        });

        this.socket.on('admin:connected', (data) => {
            console.log('✅ Connexion admin établie:', data);
            this.showSuccess('Connexion admin établie');
        });
    }

    /**
     * Initialisation de la carte
     */
    initMap() {
        if (!this.elements.mapContainer) return;

        // Configuration de la carte (Leaflet)
        this.map = L.map('admin-map').setView([5.316667, -4.033333], 10); // Abidjan par défaut

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);

        // Gestion du clic sur la carte
        this.map.on('click', (e) => {
            console.log('Clic sur la carte:', e.latlng);
        });
    }

    /**
     * Charger les données des livreurs
     */
    async loadLivreursData() {
        try {
            const response = await fetch(`${this.config.apiUrl}/admin/location/livreurs`, {
                headers: {
                    'Authorization': `Bearer ${this.config.token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.updateLivreursList(data.data);
                this.updateMap(data.data);
                this.updateStats(data.data);
            }
        } catch (error) {
            console.error('Erreur chargement données livreurs:', error);
        }
    }

    /**
     * Ajouter un livreur
     */
    addLivreur(livreurData) {
        this.livreurs.set(livreurData.livreur_id, {
            ...livreurData,
            status: 'online',
            lastUpdate: new Date()
        });

        this.updateLivreursList();
        this.showSuccess(`Livreur ${livreurData.livreur_name} en ligne`);
    }

    /**
     * Supprimer un livreur
     */
    removeLivreur(livreurId) {
        this.livreurs.delete(livreurId);

        // Supprimer le marqueur de la carte
        if (this.markers.has(livreurId)) {
            this.map.removeLayer(this.markers.get(livreurId));
            this.markers.delete(livreurId);
        }

        this.updateLivreursList();
    }

    /**
     * Mettre à jour la position d'un livreur
     */
    updateLivreurLocation(locationData) {
        const livreurId = locationData.livreur_id;

        if (this.livreurs.has(livreurId)) {
            const livreur = this.livreurs.get(livreurId);
            livreur.latitude = locationData.latitude;
            livreur.longitude = locationData.longitude;
            livreur.accuracy = locationData.accuracy;
            livreur.speed = locationData.speed;
            livreur.heading = locationData.heading;
            livreur.timestamp = locationData.timestamp;
            livreur.lastUpdate = new Date();

            this.updateLivreurMarker(livreur);
        }
    }

    /**
     * Mettre à jour le statut d'un livreur
     */
    updateLivreurStatus(statusData) {
        const livreurId = statusData.livreur_id;

        if (this.livreurs.has(livreurId)) {
            const livreur = this.livreurs.get(livreurId);
            livreur.status = statusData.status;
            livreur.lastUpdate = new Date();

            this.updateLivreursList();
            this.updateLivreurMarker(livreur);
        }
    }

    /**
     * Mettre à jour la liste des livreurs
     */
    updateLivreursList() {
        if (!this.elements.livreursList) return;

        const livreursArray = Array.from(this.livreurs.values());

        this.elements.livreursList.innerHTML = livreursArray.map(livreur => `
            <div class="livreur-item ${livreur.status}" data-livreur-id="${livreur.livreur_id}">
                <div class="livreur-header">
                    <h4>${livreur.livreur_name}</h4>
                    <span class="status-badge ${livreur.status}">${this.getStatusText(livreur.status)}</span>
                </div>
                <div class="livreur-details">
                    <div class="livreur-coords">
                        <strong>Position:</strong> ${livreur.latitude ? livreur.latitude.toFixed(6) : 'N/A'}, ${livreur.longitude ? livreur.longitude.toFixed(6) : 'N/A'}
                    </div>
                    <div class="livreur-speed">
                        <strong>Vitesse:</strong> ${livreur.speed ? (livreur.speed * 3.6).toFixed(2) + ' km/h' : 'N/A'}
                    </div>
                    <div class="livreur-accuracy">
                        <strong>Précision:</strong> ${livreur.accuracy ? livreur.accuracy.toFixed(2) + 'm' : 'N/A'}
                    </div>
                    <div class="livreur-time">
                        <strong>Dernière mise à jour:</strong> ${livreur.lastUpdate ? livreur.lastUpdate.toLocaleTimeString() : 'N/A'}
                    </div>
                </div>
                <div class="livreur-actions">
                    <button class="btn btn-sm btn-primary" onclick="adminMonitor.centerOnLivreur(${livreur.livreur_id})">
                        Centrer sur carte
                    </button>
                    <button class="btn btn-sm btn-info" onclick="adminMonitor.showLivreurHistory(${livreur.livreur_id})">
                        Historique
                    </button>
                </div>
            </div>
        `).join('');
    }

    /**
     * Mettre à jour la carte
     */
    updateMap(livreursData) {
        if (!this.map) return;

        // Supprimer tous les marqueurs existants
        this.markers.forEach(marker => {
            this.map.removeLayer(marker);
        });
        this.markers.clear();

        // Ajouter les nouveaux marqueurs
        livreursData.forEach(livreur => {
            if (livreur.latitude && livreur.longitude) {
                this.addLivreurMarker(livreur);
            }
        });
    }

    /**
     * Ajouter un marqueur de livreur
     */
    addLivreurMarker(livreur) {
        const icon = this.getLivreurIcon(livreur.status);

        const marker = L.marker([livreur.latitude, livreur.longitude], { icon })
            .bindPopup(`
                <div class="livreur-popup">
                    <h4>${livreur.livreur_name}</h4>
                    <p><strong>Statut:</strong> ${this.getStatusText(livreur.status)}</p>
                    <p><strong>Position:</strong> ${livreur.latitude.toFixed(6)}, ${livreur.longitude.toFixed(6)}</p>
                    <p><strong>Vitesse:</strong> ${livreur.speed ? (livreur.speed * 3.6).toFixed(2) + ' km/h' : 'N/A'}</p>
                    <p><strong>Précision:</strong> ${livreur.accuracy ? livreur.accuracy.toFixed(2) + 'm' : 'N/A'}</p>
                    <p><strong>Dernière mise à jour:</strong> ${new Date(livreur.timestamp).toLocaleString()}</p>
                </div>
            `);

        marker.addTo(this.map);
        this.markers.set(livreur.livreur_id, marker);
    }

    /**
     * Mettre à jour le marqueur d'un livreur
     */
    updateLivreurMarker(livreur) {
        if (this.markers.has(livreur.livreur_id)) {
            const marker = this.markers.get(livreur.livreur_id);
            marker.setLatLng([livreur.latitude, livreur.longitude]);

            // Mettre à jour l'icône si le statut a changé
            const newIcon = this.getLivreurIcon(livreur.status);
            marker.setIcon(newIcon);
        }
    }

    /**
     * Obtenir l'icône d'un livreur selon son statut
     */
    getLivreurIcon(status) {
        const iconConfig = {
            active: { color: 'green', icon: 'truck' },
            inactive: { color: 'red', icon: 'user' },
            paused: { color: 'orange', icon: 'pause' }
        };

        const config = iconConfig[status] || iconConfig.inactive;

        return L.divIcon({
            className: 'livreur-marker',
            html: `<div class="marker-icon ${config.color}">${config.icon}</div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });
    }

    /**
     * Centrer la carte sur un livreur
     */
    centerOnLivreur(livreurId) {
        if (this.livreurs.has(livreurId)) {
            const livreur = this.livreurs.get(livreurId);
            if (livreur.latitude && livreur.longitude) {
                this.map.setView([livreur.latitude, livreur.longitude], 15);

                // Ouvrir le popup du marqueur
                if (this.markers.has(livreurId)) {
                    this.markers.get(livreurId).openPopup();
                }
            }
        }
    }

    /**
     * Afficher l'historique d'un livreur
     */
    async showLivreurHistory(livreurId) {
        try {
            const response = await fetch(`${this.config.apiUrl}/admin/location/history?livreur_id=${livreurId}`, {
                headers: {
                    'Authorization': `Bearer ${this.config.token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.displayLivreurHistory(data.data);
            }
        } catch (error) {
            console.error('Erreur récupération historique:', error);
            this.showError('Erreur lors du chargement de l\'historique');
        }
    }

    /**
     * Afficher l'historique d'un livreur
     */
    displayLivreurHistory(historyData) {
        // Créer une modal pour afficher l'historique
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Historique des positions</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="history-list">
                            ${historyData.map(item => `
                                <div class="history-item">
                                    <div class="history-time">${new Date(item.timestamp).toLocaleString()}</div>
                                    <div class="history-coords">${item.latitude.toFixed(6)}, ${item.longitude.toFixed(6)}</div>
                                    <div class="history-details">
                                        <span class="badge bg-${item.status === 'en_cours' ? 'success' : item.status === 'en_pause' ? 'warning' : 'secondary'}">${item.status}</span>
                                        ${item.accuracy ? `<span>Précision: ${item.accuracy.toFixed(2)}m</span>` : ''}
                                        ${item.speed ? `<span>Vitesse: ${(item.speed * 3.6).toFixed(2)} km/h</span>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Afficher la modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Supprimer la modal après fermeture
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Mettre à jour les statistiques
     */
    updateStats(livreursData) {
        if (!this.elements.statsContainer) return;

        const stats = {
            total: livreursData.length,
            active: livreursData.filter(l => l.status === 'en_cours').length,
            paused: livreursData.filter(l => l.status === 'en_pause').length,
            completed: livreursData.filter(l => l.status === 'termine').length
        };

        this.elements.statsContainer.innerHTML = `
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">${stats.total}</div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-success">${stats.active}</div>
                    <div class="stat-label">Actifs</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-warning">${stats.paused}</div>
                    <div class="stat-label">En pause</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value text-secondary">${stats.completed}</div>
                    <div class="stat-label">Terminés</div>
                </div>
            </div>
        `;
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
     * Obtenir le texte du statut
     */
    getStatusText(status) {
        const statusMap = {
            'en_cours': 'En cours',
            'en_pause': 'En pause',
            'termine': 'Terminé',
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
     * Déconnexion
     */
    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
        }
    }
}

// Export pour utilisation globale
window.AdminLocationMonitor = AdminLocationMonitor;
