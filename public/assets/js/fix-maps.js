/**
 * Script de correction pour les cartes de géolocalisation
 * MOYOO Delivery - Fix des cartes qui ne s'affichent pas
 */

// Fonction pour initialiser la carte livreur
function initLivreurMap() {
    console.log('🗺️  Initialisation de la carte livreur...');

    try {
        // Vérifier que Leaflet est chargé
        if (typeof L === 'undefined') {
            console.error('❌ Leaflet n\'est pas chargé');
            return false;
        }

        // Vérifier que le conteneur existe
        const mapContainer = document.getElementById('map-container');
        if (!mapContainer) {
            console.error('❌ Conteneur map-container non trouvé');
            return false;
        }

        // Initialiser la carte
        const map = L.map('map-container').setView([5.316667, -4.033333], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur de test
        const marker = L.marker([5.316667, -4.033333]).addTo(map);
        marker.bindPopup('Position actuelle').openPopup();

        console.log('✅ Carte livreur initialisée avec succès');
        return true;

    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation de la carte livreur:', error);
        return false;
    }
}

// Fonction pour initialiser la carte admin
function initAdminMap() {
    console.log('🗺️  Initialisation de la carte admin...');

    try {
        // Vérifier que Leaflet est chargé
        if (typeof L === 'undefined') {
            console.error('❌ Leaflet n\'est pas chargé');
            return false;
        }

        // Vérifier que le conteneur existe
        const mapContainer = document.getElementById('admin-map');
        if (!mapContainer) {
            console.error('❌ Conteneur admin-map non trouvé');
            return false;
        }

        // Initialiser la carte
        const map = L.map('admin-map').setView([5.316667, -4.033333], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur de test
        const marker = L.marker([5.316667, -4.033333]).addTo(map);
        marker.bindPopup('Centre de suivi').openPopup();

        console.log('✅ Carte admin initialisée avec succès');
        return true;

    } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation de la carte admin:', error);
        return false;
    }
}

// Fonction pour vérifier la connexion Socket.IO
function checkSocketConnection() {
    console.log('🔌 Vérification de la connexion Socket.IO...');

    try {
        // Vérifier que Socket.IO est chargé
        if (typeof io === 'undefined') {
            console.error('❌ Socket.IO n\'est pas chargé');
            return false;
        }

        // Tenter une connexion
        const socket = io('http://192.168.1.6:3001', {
            auth: {
                token: 'test-token',
                userId: '1',
                userName: 'Test User',
                userRole: 'livreur'
            }
        });

        socket.on('connect', () => {
            console.log('✅ Connexion Socket.IO établie');
        });

        socket.on('connect_error', (error) => {
            console.error('❌ Erreur de connexion Socket.IO:', error);
        });

        return true;

    } catch (error) {
        console.error('❌ Erreur lors de la vérification Socket.IO:', error);
        return false;
    }
}

// Fonction principale de test
function testMaps() {
    console.log('🧪 Test des cartes de géolocalisation...');

    // Attendre que le DOM soit chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', testMaps);
        return;
    }

    // Test de la carte livreur
    if (document.getElementById('map-container')) {
        console.log('📱 Test de la carte livreur...');
        initLivreurMap();
    }

    // Test de la carte admin
    if (document.getElementById('admin-map')) {
        console.log('👨‍💼 Test de la carte admin...');
        initAdminMap();
    }

    // Test de la connexion Socket.IO
    checkSocketConnection();

    console.log('🎉 Test des cartes terminé !');
}

// Démarrer le test
testMaps();
