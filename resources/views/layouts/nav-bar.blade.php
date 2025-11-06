<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
id="layout-navbar">
<div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
  <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
    <i class="ti ti-menu-2 ti-md"></i>
  </a>
</div>

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
  <!-- Search -->
  <div class="navbar-nav align-items-center">
    <div class="nav-item navbar-search-wrapper mb-0">
      <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
        <i class="ti ti-search ti-md me-2 me-lg-4 ti-lg"></i>
        <span class="d-none d-md-inline-block text-muted fw-normal">Search (Ctrl+/)</span>
      </a>
    </div>
  </div>
  <!-- /Search -->

  <ul class="navbar-nav flex-row align-items-center ms-auto">
    <!-- Language -->
    <li class="nav-item dropdown-language dropdown">
      <a
        class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
        href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <i class="ti ti-language rounded-circle ti-md"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-language="en" data-text-direction="ltr">
            <span>English</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-language="fr" data-text-direction="ltr">
            <span>French</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-language="ar" data-text-direction="rtl">
            <span>Arabic</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-language="de" data-text-direction="ltr">
            <span>German</span>
          </a>
        </li>
      </ul>
    </li>
    <!--/ Language -->

    <!-- Style Switcher -->
    <li class="nav-item dropdown-style-switcher dropdown">
      <a
        class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
        href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <i class="ti ti-md"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
            <span class="align-middle"><i class="ti ti-sun ti-md me-3"></i>Light</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
            <span class="align-middle"><i class="ti ti-moon-stars ti-md me-3"></i>Dark</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
            <span class="align-middle"
              ><i class="ti ti-device-desktop-analytics ti-md me-3"></i>System</span
            >
          </a>
        </li>
      </ul>
    </li>
    <!-- / Style Switcher-->

    <!-- Notification -->
    <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
      <a
        class="nav-link btn btn-text-secondary btn-icon rounded-pill dropdown-toggle hide-arrow"
        href="javascript:void(0);"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <span class="position-relative">
          <i class="ti ti-bell ti-md"></i>
          <span class="badge rounded-pill bg-danger badge-dot badge-notifications border" id="notification-badge" style="display: none;"></span>
        </span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end p-0">
        <li class="dropdown-menu-header border-bottom">
          <div class="dropdown-header d-flex align-items-center py-3">
            <h6 class="mb-0 me-auto">Notification</h6>
            <div class="d-flex align-items-center h6 mb-0">
              <span class="badge bg-label-primary me-2" id="notification-count">0</span>
              <a
                href="javascript:void(0)"
                class="btn btn-text-secondary rounded-pill btn-icon dropdown-notifications-all"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Mark all as read"
                ><i class="ti ti-mail-opened text-heading"></i
              ></a>
            </div>
          </div>
        </li>
        <li class="dropdown-notifications-list scrollable-container">
          <ul class="list-group list-group-flush" id="notifications-list">
            <!-- Les notifications seront chargées dynamiquement par JavaScript -->
          </ul>
        </li>
        <li class="border-top">
          <div class="d-grid p-4">
            <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);" id="mark-all-read-btn">
              <small class="align-middle">Marquer tout comme lu</small>
            </a>
          </div>
        </li>
      </ul>
    </li>
    <!--/ Notification -->

    <!-- Script pour les notifications en temps réel -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Variables globales
        let notificationCount = 0;
        let notifications = [];

        // Fonction pour charger les notifications
        async function loadNotifications() {
            try {
                // Afficher un indicateur de chargement
                showLoadingState();

                const response = await fetch('/api/notifications?limit=10');
                const data = await response.json();

                if (data.success) {
                    notifications = data.data;
                    notificationCount = data.unread_count;
                    updateNotificationUI();
                } else {
                    console.error('Erreur API:', data.message);
                    showErrorState();
                }
            } catch (error) {
                console.error('Erreur lors du chargement des notifications:', error);
                showErrorState();
            }
        }

        // Fonction pour afficher l'état de chargement
        function showLoadingState() {
            const listElement = document.getElementById('notifications-list');
            if (listElement) {
                listElement.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <div class="text-muted">
                            <div class="spinner-border spinner-border-sm me-2" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            Chargement des notifications...
                        </div>
                    </li>
                `;
            }
        }

        // Fonction pour afficher l'état d'erreur
        function showErrorState() {
            const listElement = document.getElementById('notifications-list');
            if (listElement) {
                listElement.innerHTML = `
                    <li class="list-group-item text-center py-4">
                        <div class="text-muted">
                            <i class="ti ti-alert-circle ti-lg mb-2"></i>
                            <p class="mb-0">Erreur lors du chargement</p>
                            <small>Veuillez réessayer plus tard</small>
                        </div>
                    </li>
                `;
            }
        }

        // Fonction pour mettre à jour l'interface
        function updateNotificationUI() {
            // Mettre à jour le compteur dans le dropdown
            const countElement = document.getElementById('notification-count');
            if (countElement) {
                countElement.textContent = notificationCount;
                countElement.style.display = notificationCount > 0 ? 'inline' : 'none';
            }

            // Mettre à jour le badge rouge sur l'icône de la cloche
            const badgeElement = document.getElementById('notification-badge');
            if (badgeElement) {
                badgeElement.style.display = notificationCount > 0 ? 'inline' : 'none';
            }

            // Mettre à jour la liste des notifications
            const listElement = document.getElementById('notifications-list');
            if (listElement) {
                listElement.innerHTML = '';

                if (notifications.length === 0) {
                    listElement.innerHTML = `
                        <li class="list-group-item text-center py-4">
                            <div class="text-muted">
                                <i class="ti ti-bell-off ti-lg mb-2"></i>
                                <p class="mb-0">Aucune notification</p>
                                <small>Toutes vos notifications apparaîtront ici</small>
                            </div>
                        </li>
                    `;
                } else {
                    notifications.forEach(notification => {
                        const isRead = notification.read_at !== null;
                        const notificationData = notification.data;

                        // Déterminer la couleur et l'icône selon le type
                        let color = 'primary';
                        let icon = 'ti-bell';

                        if (notificationData.type === 'delivery_completed') {
                            color = 'success';
                            icon = 'ti-truck';
                        } else if (notificationData.type === 'pickup_completed') {
                            color = 'info';
                            icon = 'ti-package';
                        } else if (notificationData.type === 'new_colis') {
                            color = 'primary';
                            icon = 'ti-package';
                        }

                        listElement.innerHTML += `
                            <li class="list-group-item list-group-item-action dropdown-notifications-item ${isRead ? 'marked-as-read' : ''}"
                                data-notification-id="${notification.id}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle bg-label-${color}">
                                                <i class="ti ${icon}"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small">${notificationData.title || 'Notification'}</h6>
                                        <small class="mb-1 d-block text-body">${notificationData.message || ''}</small>
                                        <small class="text-muted">${formatTime(notification.created_at)}</small>
                                    </div>
                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                        ${!isRead ? `
                                            <a href="javascript:void(0)" class="dropdown-notifications-read"
                                               onclick="markAsRead('${notification.id}')" title="Marquer comme lu">
                                                <span class="badge badge-dot"></span>
                                            </a>
                                        ` : ''}
                                        <a href="javascript:void(0)" class="dropdown-notifications-archive"
                                           onclick="deleteNotification('${notification.id}')" title="Supprimer">
                                            <span class="ti ti-x"></span>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                }
            }
        }

        // Fonction pour formater le temps
        function formatTime(timestamp) {
            const now = new Date();
            const time = new Date(timestamp);
            const diff = now - time;

            if (diff < 60000) return 'À l\'instant';
            if (diff < 3600000) return `${Math.floor(diff / 60000)}min`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)}h`;
            return `${Math.floor(diff / 86400000)}j`;
        }

        // Fonction pour marquer comme lu
        async function markAsRead(notificationId) {
            try {
                const response = await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Recharger les notifications
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Erreur lors du marquage comme lu:', error);
            }
        }

        // Fonction pour supprimer une notification
        async function deleteNotification(notificationId) {
            try {
                const response = await fetch(`/api/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Recharger les notifications
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
            }
        }

        // Fonction pour marquer toutes comme lues
        async function markAllAsRead() {
            try {
                const response = await fetch('/api/notifications/mark-all-read', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    // Recharger les notifications
                    await loadNotifications();
                }
            } catch (error) {
                console.error('Erreur lors du marquage de toutes les notifications:', error);
            }
        }

        // Event listeners
        document.getElementById('mark-all-read-btn')?.addEventListener('click', markAllAsRead);

        // Charger les notifications au chargement de la page
        loadNotifications();

        // Recharger les notifications toutes les 30 secondes
        setInterval(loadNotifications, 30000);

        // Exposer les fonctions globalement
        window.markAsRead = markAsRead;
        window.deleteNotification = deleteNotification;
    });
    </script>

    <!-- User -->
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a
        class="nav-link dropdown-toggle hide-arrow p-0"
        href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <span class="avatar-initial rounded bg-label-primary">
            {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
          </span>
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item mt-0" href="">
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <span class="avatar-initial rounded bg-label-primary">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                  </span>
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h6>
                <small class="text-muted">
                  Super Admin
                </small>
              </div>
            </div>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item" href="">
            <i class="ti ti-user me-3 ti-md"></i><span class="align-middle">Mon Profil</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="">
            <i class="ti ti-key me-3 ti-md"></i><span class="align-middle">Changer le Mot de Passe</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="">
            <span class="d-flex align-items-center align-middle">
              <i class="flex-shrink-0 ti ti-file-dollar me-3 ti-md"></i
              ><span class="flex-grow-1 align-middle">Historique d'abonnement</span>
              <span class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center"
                >3</span
              >
            </span>
          </a>
        </li>
        <li>
          <div class="dropdown-divider my-1 mx-n2"></div>
        </li>
        <li>
          <a class="dropdown-item" href="">
            <i class="ti ti-currency-dollar me-3 ti-md"></i><span class="align-middle">Forfaits</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="">
            <i class="ti ti-question-mark me-3 ti-md"></i><span class="align-middle">FAQ</span>
          </a>
        </li>
        <li></li>
          <div class="d-grid px-2 pt-2 pb-1">
            <form method="POST" action="" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-sm btn-danger d-flex w-100" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                <small class="align-middle">Logout</small>
                <i class="ti ti-logout ms-2 ti-14px"></i>
              </button>
            </form>
          </div>
        </li>
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>

<!-- Search Small Screens -->
<div class="navbar-search-wrapper search-input-wrapper d-none">
  <input
    type="text"
    class="form-control search-input container-xxl border-0"
    placeholder="Search..."
    aria-label="Search..." />
  <i class="ti ti-x search-toggler cursor-pointer"></i>
</div>
</nav>
