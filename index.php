
<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
// 어떤 API가 어디에 가서 어떤 로직을 수행할 지
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    // addRoute('111', '/222', ['333', '444']);
    // 111 method로 /222 경로의 333.php파일로 가서 444 함수 실행
//    $r->addRoute('GET', '/', ['IndexController', 'index']);
//    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
//    $r->addRoute('GET', '/users/{no}', ['IndexController', 'getUserDetail']);3
//    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
//    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
//    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);




    // 1. 찜한 음식점
    // ex) indabaesori.shop/restaurant/favorite?user_id=10000001
    $r->addRoute('GET', '/restaurant/favorite', ['IndexController', 'getFavorite']);

    // 2.1. 전체 음식점
    // ex) indabaesori.shop/restaurant/all?user_id=10000001
    $r->addRoute('GET', '/restaurant/all', ['IndexController', 'getAllRestaurant']);

    // 2.2. 1인분 가능 음식점
    $r->addRoute('GET', '/restaurant/portion', ['IndexController', 'getPortionRestaurant']);

    // 2.3. 야식 가능 음식점
    $r->addRoute('GET', '/restaurant/night', ['IndexController', 'getNightRestaurant']);

    // 2.4. 프랜차이즈 음식점
    $r->addRoute('GET', '/restaurant/franchise', ['IndexController', 'getFranchiseRestaurant']);

    // 2.5. 카테고리별 음식점
    // ex) indabaesori.shop/restaurant?category=치킨&user_id=10000001
    $r->addRoute('GET', '/restaurant', ['IndexController', 'getRestaurantByCategory']);

    // 3. 음식점 정보 (식당 정보, 메뉴(인기메뉴, 카테고리별), 리뷰)
    // ex) indabaesori.shop/restaurant/20000001
    $r->addRoute('GET', '/restaurant/{rest_id}', ['IndexController', 'getRestaurantDetail']);

    // 4. 메뉴 추가 옵션 선택
    // ex) indabaesori.shop/menu/30000001
    $r->addRoute('GET', '/menu/{menu_id}', ['IndexController', 'getMenuOption']);

    // 5. 주문내역
    // ex) indabaesori.shop/order?user_id=10000001
    $r->addRoute('GET', '/order', ['IndexController', 'getOrderList']);

    // 6. 주문 상세 보기
    // ex) indabaesori.shop/order/200613-20-144783
    $r->addRoute('GET', '/order/{order_id}', ['IndexController', 'getOrderDetail']);

    // 7. 마이요기요
    // ex) indabaesori.shop/my-yogiyo?user_id=10000001
    $r->addRoute('GET', '/my-yogiyo', ['IndexController', 'getMyYogiyo']);

    // 8. 사용자 정보
    // ex) indabaesori.shop/my-yogiyo/user-info?user_id=10000001
    $r->addRoute('GET', '/my-yogiyo/user-info', ['IndexController', 'getUserInfo']);

    // 9. 등록한 카드
    // ex) indabaesori.shop/my-yogiyo/card?user_id=10000001
    $r->addRoute('GET', '/my-yogiyo/card', ['IndexController', 'getCardInfo']);

    // 10. 메뉴 검색
    // ex) indabaesori.shop/search?keyword=치킨
    $r->addRoute('GET', '/search', ['IndexController', 'findMenu']);

    // 11. 회원가입
    $r->addRoute('POST', '/sign-up', ['IndexController', 'addUser']);
//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
            case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
