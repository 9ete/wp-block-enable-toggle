import './commands';

// The WordPress admin and block editor emit unrelated runtime exceptions that
// would otherwise fail tests. We are only testing this plugin's behaviour.
Cypress.on( 'uncaught:exception', () => false );
