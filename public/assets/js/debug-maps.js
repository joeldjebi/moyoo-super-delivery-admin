/**
 * Script de débogage pour les cartes de géolocalisation
 * MOYOO Delivery - Diagnostic des problèmes d'affichage
 */

console.log('🔍 Démarrage du diagnostic des cartes...');

// Fonction de diagnostic
function diagnoseMapIssues() {
    console.log('📊 Diagnostic des problèmes de cartes...');
    
    // 1. Vérifier Leaflet
    if (typeof L === 'undefined') {
        console.error('❌ PROBLÈME: Leaflet n\'est pas chargé');
        return false;
    } else {
        console.log('✅ Leaflet chargé:', L.version);
    }
    
    // 2. Vérifier les conteneurs
    const mapContainer = document.getElementById('map-container');
    const adminContainer = document.getElementById('admin-map');
    
    if (!mapContainer) {
        console.error('❌ PROBLÈME: Conteneur map-container non trouvé');
    } else {
        console.log('✅ Conteneur map-container trouvé:', {
            width: mapContainer.offsetWidth,
            height: mapContainer.offsetHeight,
            visible: mapContainer.offsetHeight > 0
        });
    }
    
    if (!adminContainer) {
        console.error('❌ PROBLÈME: Conteneur admin-map non trouvé');
    } else {
        console.log('✅ Conteneur admin-map trouvé:', {
            width: adminContainer.offsetWidth,
            height: adminContainer.offsetHeight,
            visible: adminContainer.offsetHeight > 0
        });
    }
    
    // 3. Vérifier les styles CSS
    const styles = document.querySelectorAll('style');
    let hasMapStyles = false;
    styles.forEach(style => {
        if (style.textContent.includes('map-container') || style.textContent.includes('admin-map')) {
            hasMapStyles = true;
        }
    });
    
    if (hasMapStyles) {
        console.log('✅ Styles CSS pour les cartes trouvés');
    } else {
        console.warn('⚠️ Styles CSS pour les cartes non trouvés');
    }
    
    // 4. Vérifier les scripts
    const scripts = document.querySelectorAll('script');
    let hasLeafletScript = false;
    let hasLocationScript = false;
    
    scripts.forEach(script => {
        if (script.src && script.src.includes('leaflet')) {
            hasLeafletScript = true;
        }
        if (script.src && (script.src.includes('location-tracker') || script.src.includes('admin-location-monitor'))) {
            hasLocationScript = true;
        }
    });
    
    if (hasLeafletScript) {
        console.log('✅ Script Leaflet chargé');
    } else {
        console.error('❌ PROBLÈME: Script Leaflet non chargé');
    }
    
    if (hasLocationScript) {
        console.log('✅ Scripts de géolocalisation chargés');
    } else {
        console.warn('⚠️ Scripts de géolocalisation non chargés');
    }
    
    return true;
}

// Fonction pour tester l'initialisation d'une carte
function testMapInitialization(containerId, mapName) {
    console.log(`🧪 Test d'initialisation de la carte ${mapName}...`);
    
    try {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`❌ Conteneur ${containerId} non trouvé`);
            return false;
        }
        
        if (container.offsetHeight === 0) {
            console.warn(`⚠️ Conteneur ${containerId} a une hauteur de 0`);
        }
        
        // Créer une carte de test
        const map = L.map(containerId).setView([5.316667, -4.033333], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        const marker = L.marker([5.316667, -4.033333]).addTo(map);
        marker.bindPopup(`Test ${mapName}`).openPopup();
        
        // Forcer le redimensionnement
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
        
        console.log(`✅ Carte ${mapName} initialisée avec succès`);
        return true;
        
    } catch (error) {
        console.error(`❌ Erreur lors de l'initialisation de la carte ${mapName}:`, error);
        return false;
    }
}

// Fonction principale de diagnostic
function runDiagnostic() {
    console.log('🚀 Démarrage du diagnostic complet...');
    
    // Attendre que le DOM soit chargé
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runDiagnostic);
        return;
    }
    
    // Diagnostic initial
    const diagnosticOk = diagnoseMapIssues();
    
    if (!diagnosticOk) {
        console.error('❌ Diagnostic échoué - problèmes détectés');
        return;
    }
    
    // Attendre un peu puis tester les cartes
    setTimeout(() => {
        console.log('🧪 Test des cartes...');
        
        const livreurOk = testMapInitialization('map-container', 'Livreur');
        
        setTimeout(() => {
            const adminOk = testMapInitialization('admin-map', 'Admin');
            
            if (livreurOk && adminOk) {
                console.log('🎉 Toutes les cartes fonctionnent correctement !');
            } else {
                console.error('❌ Certaines cartes ont des problèmes');
            }
        }, 500);
    }, 1000);
}

// Démarrer le diagnostic
runDiagnostic();
