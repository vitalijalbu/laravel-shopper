<?php

// Esempio di come dovresti configurare le traduzioni nel tuo controller Laravel
// Questo file mostra come passare le traduzioni a Inertia

return [
    'shopper' => [
        'auth' => [
            'headings' => [
                'login' => 'Accedi al Control Panel',
            ],
            'descriptions' => [
                'login' => 'Inserisci le tue credenziali per accedere',
            ],
            'labels' => [
                'email' => 'Email',
                'password' => 'Password',
                'remember_me' => 'Ricordami',
                'forgot_password' => 'Password dimenticata?',
                'login' => 'Accedi',
            ],
            'placeholders' => [
                'email' => 'inserisci@email.com',
                'password' => 'La tua password',
            ],
            'actions' => [
                'signing_in' => 'Accesso in corso...',
            ],
        ],
    ],
    'apps' => [
        'installed' => [
            'title' => 'App Installate',
            'active' => 'Attive',
            'inactive' => 'Inattive',
            'no_apps' => 'Nessuna app installata',
            'install_first_app' => 'Installa la tua prima app dal marketplace',
        ],
        'store' => [
            'browse' => 'Sfoglia Store',
            'search_placeholder' => 'Cerca app...',
        ],
        'filters' => [
            'all' => 'Tutte',
        ],
        'loading' => 'Caricamento...',
        'selected_count' => '{count} selezionate',
        'actions' => [
            'activate_selected' => 'Attiva selezionate',
            'deactivate_selected' => 'Disattiva selezionate',
            'uninstall_selected' => 'Disinstalla selezionate',
        ],
        'messages' => [
            'confirm_uninstall' => 'Sei sicuro di voler disinstallare {name}?',
            'confirm_bulk_uninstall' => 'Sei sicuro di voler disinstallare {count} app?',
        ],
    ],
];
