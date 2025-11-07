        <!-- Menu -->

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
              <a href="index.html" class="app-brand-link">
                <span class="app-brand-logo demo">
                  <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                      fill="#7367F0" />
                    <path
                      opacity="0.06"
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                      fill="#161616" />
                    <path
                      opacity="0.06"
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                      fill="#161616" />
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                      fill="#7367F0" />
                  </svg>
                </span>
                <span class="app-brand-text demo menu-text fw-bold">MOYOO fleet</span>
              </a>

              <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-md align-middle"></i>
              </a>
            </div>

            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                @php
                    $user = Auth::guard('platform_admin')->user();
                @endphp

                <!-- Tableau de bord -->
                @if($user && $user->hasPermission('dashboard.read'))
                    <li class="menu-item {{ isset($menu) && $menu == 'dashboard' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-smart-home"></i>
                        <div data-i18n="Tableau de bord">Tableau de bord</div>
                      </a>
                    </li>
                @endif

                @php
                    $hasGestionItems = ($user && $user->hasPermission('entreprises.read')) ||
                                      ($user && $user->hasPermission('users.read'));
                @endphp

                @if($hasGestionItems)
                    <li class="menu-header small">
                      <span class="menu-header-text">Gestion</span>
                    </li>
                @endif

                <!-- Entreprises -->
                @if($user && $user->hasPermission('entreprises.read'))
                    <li class="menu-item {{ isset($menu) && $menu == 'entreprises' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.entreprises.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-building"></i>
                        <div data-i18n="Entreprises">Entreprises</div>
                      </a>
                    </li>
                @endif

                <!-- Utilisateurs -->
                @if($user && $user->hasPermission('users.read'))
                    <li class="menu-item {{ isset($menu) && $menu == 'users' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-users"></i>
                        <div data-i18n="Utilisateurs">Utilisateurs</div>
                      </a>
                    </li>
                @endif

                @php
                    $hasAdminItems = ($user && $user->hasPermission('admins.read')) ||
                                    ($user && $user->hasPermission('roles.read')) ||
                                    ($user && $user->hasPermission('permissions.read'));
                @endphp

                @if($hasAdminItems)
                    <li class="menu-header small">
                      <span class="menu-header-text">Administration</span>
                    </li>
                @endif

                <!-- Administrateurs -->
                @if($user && $user->hasPermission('admins.read'))
                    <li class="menu-item {{ isset($menu) && ($menu == 'admin-users' || str_contains(request()->route()->getName() ?? '', 'admin-users')) ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.admin-users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-user-shield"></i>
                        <div data-i18n="Administrateurs">Administrateurs</div>
                      </a>
                    </li>
                @endif

                <!-- Rôles -->
                @if($user && $user->hasPermission('roles.read'))
                    <li class="menu-item {{ isset($menu) && ($menu == 'roles' || str_contains(request()->route()->getName() ?? '', 'roles')) ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.roles.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-user-circle"></i>
                        <div data-i18n="Rôles">Rôles</div>
                      </a>
                    </li>
                @endif

                <!-- Permissions -->
                @if($user && $user->hasPermission('permissions.read'))
                    <li class="menu-item {{ isset($menu) && ($menu == 'permissions' || str_contains(request()->route()->getName() ?? '', 'permissions')) ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.permissions.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-shield-check"></i>
                        <div data-i18n="Permissions">Permissions</div>
                      </a>
                    </li>
                @endif

                @php
                    $hasSubscriptionItems = ($user && $user->hasPermission('pricing_plans.read')) ||
                                           ($user && $user->hasPermission('subscriptions.read'));
                @endphp

                @if($hasSubscriptionItems)
                    <li class="menu-header small">
                      <span class="menu-header-text">Abonnements</span>
                    </li>
                @endif

                <!-- Plans tarifaires -->
                @if($user && $user->hasPermission('pricing_plans.read'))
                    <li class="menu-item {{ isset($menu) && $menu == 'pricing-plans' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.pricing-plans.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-currency-dollar"></i>
                        <div data-i18n="Plans tarifaires">Plans tarifaires</div>
                      </a>
                    </li>
                @endif

                <!-- Abonnements -->
                @if($user && $user->hasPermission('subscriptions.read'))
                    <li class="menu-item {{ isset($menu) && $menu == 'subscriptions' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.subscriptions.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-crown"></i>
                        <div data-i18n="Abonnements">Abonnements</div>
                      </a>
                    </li>

                    <!-- Historique des upgrades -->
                    <li class="menu-item {{ isset($menu) && $menu == 'subscriptions-upgrade-history' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.subscriptions.upgrade-history') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-history"></i>
                        <div data-i18n="Historique des upgrades">Historique des upgrades</div>
                      </a>
                    </li>
                @endif

                @php
                    $hasGlobalDataItems = ($user && $user->hasPermission('global_data.livraisons')) ||
                                         ($user && $user->hasPermission('global_data.colis')) ||
                                         ($user && $user->hasPermission('global_data.ramassages')) ||
                                         ($user && $user->hasPermission('global_data.livreurs')) ||
                                         ($user && $user->hasPermission('global_data.boutiques'));
                @endphp

                @if($hasGlobalDataItems)
                    <li class="menu-header small">
                      <span class="menu-header-text">Données globales</span>
                    </li>
                @endif

                <!-- Livraisons globales -->
                @if($user && $user->hasPermission('global_data.livraisons'))
                    <li class="menu-item {{ isset($menu) && $menu == 'global-data-livraisons' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.global-data.livraisons') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-truck"></i>
                        <div data-i18n="Livraisons">Livraisons</div>
                      </a>
                    </li>
                @endif

                <!-- Colis globaux -->
                @if($user && $user->hasPermission('global_data.colis'))
                    <li class="menu-item {{ isset($menu) && $menu == 'global-data-colis' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.global-data.colis') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-box"></i>
                        <div data-i18n="Colis">Colis</div>
                      </a>
                    </li>
                @endif

                <!-- Ramassages globaux -->
                @if($user && $user->hasPermission('global_data.ramassages'))
                    <li class="menu-item {{ isset($menu) && $menu == 'global-data-ramassages' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.global-data.ramassages') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-package"></i>
                        <div data-i18n="Ramassages">Ramassages</div>
                      </a>
                    </li>
                @endif

                <!-- Livreurs globaux -->
                @if($user && $user->hasPermission('global_data.livreurs'))
                    <li class="menu-item {{ isset($menu) && $menu == 'global-data-livreurs' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.global-data.livreurs') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-user"></i>
                        <div data-i18n="Livreurs">Livreurs</div>
                      </a>
                    </li>
                @endif

                <!-- Boutiques globales -->
                @if($user && $user->hasPermission('global_data.boutiques'))
                    <li class="menu-item {{ isset($menu) && $menu == 'global-data-boutiques' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.global-data.boutiques') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-building-store"></i>
                        <div data-i18n="Boutiques">Boutiques</div>
                      </a>
                    </li>
                @endif

                <!-- Logs -->
                @if($user && $user->hasPermission('logs.read'))
                    <li class="menu-header small">
                      <span class="menu-header-text">Système</span>
                    </li>

                    <li class="menu-item {{ isset($menu) && $menu == 'logs' ? 'active' : '' }}">
                      <a href="{{ route('platform-admin.logs.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ti ti-file-text"></i>
                        <div data-i18n="Logs système">Logs système</div>
                      </a>
                    </li>
                @endif
              </ul>
          </aside>
          <!-- / Menu -->
                  <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            @include('layouts.nav-bar')

            <!-- / Navbar -->
                      <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
