<?PHP
/**
 * DIC (dependency Injection Container) Configuration
 */

$container = $app->getContainer();

/**
 * Session Helper class.
 */
$container['session'] = function ($c) {
    $session = new \SlimSession\Helper;
    return $session;
};

/**
 * View Renderer.
 */
$container['view'] = function ($c) {
    return new \Slim\Views\PhpRenderer('templates/');
};

/**
 * Logger.
 */
$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('matcha_log');
    $file_handler = new \Monolog\Handler\StreamHandler('logs/matcha.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

/**
 * Database Connection and Handler.
 */
$container['database'] = function ($c) {
    $db = $c['settings']['db'];
    return new DBMan($db, $c->logger);
};

/**
 * Mailing.
 */
$container['mail'] = function ($c) {
    return new Sendmail();
};

/**
 * Controllers
 */
$container['CreateProfilePost'] = function ($c) {
    return new CreateProfilePost($c->database, $c->session, $c->logger);
};

$container['SettingsController'] = function ($c) {
    return new SettingsController($c->database, $c->logger, $c->session);
};

$container['SearchController'] = function ($c) {
    return new SearchController($c->database, $c->logger, $c->view, $c->session);
};

$container['VisitController'] = function ($c) {
    return new VisitController($c->database, $c->view, $c->logger, $c->session);
};
?>