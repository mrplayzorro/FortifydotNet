<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;
use Gregwar\Captcha\CaptchaBuilder;

// Initialize Eloquent ORM
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'fortifydotnet',
    'username' => 'root',
    'password' => 'root',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Import the table structures from SQL file
$sqlPath = __DIR__ . '/fortifydotnet.sql';
if (file_exists($sqlPath)) {
    Capsule::unprepared(file_get_contents($sqlPath));
}

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Create a Laravel Container
$app = new Container();

// Set the Container as a singleton in Illuminate's Container
Container::setInstance($app);

// Create an Illuminate Events Dispatcher
$events = new Dispatcher($app);

// Create an Illuminate Router with the Dispatcher
$router = new Router($events, $app);

// Set the request instance
$request = Request::capture();
$router->setRequest($request);

// Register the session service provider
$app->register(SessionServiceProvider::class);

// Security Measure 1: IP Monitoring and Rate Limiting
$router->middleware('throttle:5,1')->get('/', function () {
    // Your web page logic here
    return 'Welcome to your website!';
});

// Security Measure 2: Captcha Prompt for High IP Counts
function is_ip_blocked($ip)
{
    $apiKey = YOURAPIKEY //Make sure that you're adding the API key.
    // Use the VPNAPI to check if the IP is associated with a VPN
    $vpnCheckUrl = "https://vpnapi.io/api/$ip?key=$apiKey";
    $response = file_get_contents($vpnCheckUrl);

    // Decode the JSON response
    $vpnInfo = json_decode($response, true);

    // Check if the IP is associated with a VPN
    if ($vpnInfo && isset($vpnInfo['security']['is_vpn']) && $vpnInfo['security']['is_vpn']) {
        return true;  // IP is associated with a VPN
    }

    // Check if the IP is blocked in your local database
    return Capsule::table('blocked_ips')->where('ip', $ip)->exists();
}

// Security Measure 3: Blocking VPNs, Proxies, and Spoofed IPs
$router->before(function () {
    if (is_ip_blocked(Request::ip())) {
        abort(403);  // Forbidden
    }
});

// Security Measure 4: Geographic Analysis
$router->before(function () {
    // Log country information for each request
    log_country_info(Request::ip());
});

// Run the application
$response = $router->dispatch($request);
$response->send();

// Function to get connected IP count
function get_connected_ip_count()
{
    return Capsule::table('connected_ips')->count();
}

// Function to check if the IP is blocked
function is_ip_blocked($ip)
{
    // Replace 'blockedIPs.sql' with the correct path to your SQL file
    $sqlPath = __DIR__ . '/blockedIPs.sql';
    
    if (file_exists($sqlPath)) {
        // Import the blocked_ips table structure from SQL file
        Capsule::unprepared(file_get_contents($sqlPath));
    }

    return Capsule::table('blocked_ips')->where('ip', $ip)->exists();
}

// Function to log country information to the database
function log_country_info($ip)
{
    // Make a request to the external API to get country information
    $apiUrl = "https://carlbot-61041ca.000webhostapp.com/index3.php?ip=$ip";
    $response = file_get_contents($apiUrl);

    // Decode the JSON response
    $apiResponse = json_decode($response, true);

    // Check if the API response contains country information
    if ($apiResponse && isset($apiResponse['country'])) {
        // Extract country information from the API response
        $country = $apiResponse['country'];

        // Insert the data into the 'country_logs' table
        Capsule::table('country_logs')->insert(['ip' => $ip, 'country' => $country]);
    } else {
        // Log as 'Unknown' if the country information is not available
        Capsule::table('country_logs')->insert(['ip' => $ip, 'country' => 'Unknown']);
    }
}


// Function to render captcha view
function render_captcha_view()
{
    // Implement logic to render captcha view
    // You may use a template engine or plain PHP
    // Sample implementation using gregwar/captcha
    $builder = new CaptchaBuilder;
    $builder->build();
    $_SESSION['captcha'] = $builder->getPhrase();
    header('Content-type: image/jpeg');
    $builder->output();
    exit;
}
