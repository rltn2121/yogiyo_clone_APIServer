
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
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);

    // JWT TEST
    $r->addRoute('GET', '/test', ['MainController', 'testJWT']);

    // 7. 최근 검색어 전체 삭제
    $r->addRoute('DELETE', '/keyword/all', ['IndexController', 'deleteAllRecentSearchKeyword']);

    // 1. 찜한 음식점 조회
    $r->addRoute('GET', '/favorite', ['IndexController', 'getFavorite']);

    // 2. 찜 하기 / 취소하기
    $r->addRoute('PATCH', '/favorite/{rest_id}', ['IndexController', 'addFavorite']);

    // 3. 카테고리별 음식점 조회
    $r->addRoute('GET', '/search/restaurant', ['IndexController', 'getRestaurantByCategory']);
    // 3. 카테고리별 음식점 조회(비회원)
    $r->addRoute('GET', '/non-member/search/restaurant', ['IndexController', 'getRestaurantByCategoryForNonmember']);
    // 4. 메뉴 검색
    $r->addRoute('GET', '/search/menu', ['IndexController', 'findMenu']);

    // 4. 메뉴 검색(비회원)
    $r->addRoute('GET', '/non-member/search/menu', ['IndexController', 'findMenuForNonmember']);

    // 5. 최근 검색어 조회
    $r->addRoute('GET', '/keyword', ['IndexController', 'getRecentSearchKeyword']);

    // 6. 최근 검색어 삭제
    $r->addRoute('DELETE', '/keyword/{idx}', ['IndexController', 'deleteRecentSearchKeyword']);



    // 8.1 특정 음식점 메인 조회
    $r->addRoute('GET', '/restaurant/{rest_id}/main', ['IndexController', 'getRestaurantMain']);

    // 8.2 특정 음식점 메뉴 조회
    $r->addRoute('GET', '/restaurant/{rest_id}/menu', ['IndexController', 'getRestaurantMenu']);

    // 8.3 특정 음식점 리뷰 조회
    $r->addRoute('GET', '/restaurant/{rest_id}/review', ['IndexController', 'getRestaurantReview']);

    // 8.4 특정 음식점 정보 조회
    $r->addRoute('GET', '/restaurant/{rest_id}/info', ['IndexController', 'getRestaurantInfo']);

    // 9. 메뉴 추가 옵션 조회
    $r->addRoute('GET', '/restaurant/{rest_id}/menu/{menu_id}', ['IndexController', 'getMenuOption']);

    // 10.1 터치주문내역 조회
    $r->addRoute('GET', '/order/touch', ['IndexController', 'getTouchOrderList']);

    // 10.2 전화주문내역 조회
    $r->addRoute('GET', '/order/call', ['IndexController', 'getCallOrderList']);

    // 11. 주문 상세 보기
    $r->addRoute('GET', '/order/{order_id}', ['IndexController', 'getOrderDetail']);

    // 12. 주문표에 메뉴 추가
    $r->addRoute('POST', '/order-pad', ['IndexController', 'addItemIntoOrderPad']);

    // 13. 주문표에 메뉴 삭제
    $r->addRoute('DELETE', '/order-pad/{order_pad_id}', ['IndexController', 'deleteItemAtOrderPad']);

    // 14. 주문표 조회
    $r->addRoute('GET', '/order-pad', ['IndexController', 'getOrderPad']);

    // 15. 주문하기
    $r->addRoute('POST', '/order', ['IndexController', 'addOrders']);

    // 15. 주문하기(비회원용)
    $r->addRoute('POST', '/non-member/order', ['IndexController', 'addOrdersForNonmember']);

    // 16. 재주문하기 (주문표에 추가)
    $r->addRoute('POST', '/re-order/{order_id}', ['IndexController', 'reOrder']);

    // 17. 마이요기요
    $r->addRoute('GET', '/my-yogiyo', ['IndexController', 'getMyYogiyo']);

    // 18. 사용자 정보
    $r->addRoute('GET', '/user-info', ['IndexController', 'getUserInfo']);

    // 19. 등록한 카드
    $r->addRoute('GET', '/card', ['IndexController', 'getCardInfo']);

    // 20. 카드 추가
    $r->addRoute('POST', '/card', ['IndexController', 'addCard']);

    // 21. 카드 삭제
    $r->addRoute('DELETE', '/card/{card_number}', ['IndexController', 'deleteCard']);

    // 22. 결제 비밀번호 변경
    $r->addRoute('PATCH', '/user-info/payment-password', ['IndexController', 'updatePaymentPassword']);

    // 23. 휴대전화 번호 변경
    $r->addRoute('PATCH', '/user-info/phone', ['IndexController', 'updatePhone']);

    // 24. 닉네임 변경
    $r->addRoute('PATCH', '/user-info/nickname', ['IndexController', 'updateNickname']);

    // 25. 배달 주소 변경
    $r->addRoute('PATCH', '/location', ['IndexController', 'updateLocation']);

    // 26. 최근 배달위치 조회
    $r->addRoute('GET', '/delivery-location', ['IndexController', 'getRecentLocation']);

    // 27. 최근 배달위치 삭제
    $r->addRoute('DELETE', '/delivery-location/{idx}', ['IndexController', 'deleteRecentLocation']);

    // 28. 회원가입
    $r->addRoute('POST', '/user-info', ['IndexController', 'addUser']);

    // 29. 회원탈퇴
    $r->addRoute('DELETE', '/user-info', ['IndexController', 'deleteUser']);

    // 30. 리뷰 작성하기
    $r->addRoute('POST', '/review', ['IndexController', 'addReview']);

    // 31. 리뷰 삭제
    $r->addRoute('DELETE', '/review/{review_id}', ['IndexController', 'deleteReview']);

    // 32. 리뷰 추천 / 취소하기
    $r->addRoute('PATCH', '/review-like/{review_id}', ['IndexController', 'addReviewLike']);

    // 33. 리뷰 신고
    $r->addRoute('POST', '/review/{review_id}', ['IndexController', 'reportReview']);

    // 34.1. 우리동네 찜 많은 음식점 (찜한 수 기준 정렬)
    $r->addRoute('GET', '/main/favorite-most', ['IndexController', 'getFavoriteMostRestaurant']);

    // 35.1. 학익동 오늘만 할인 (배달할인 or 할인율 > 0)
    $r->addRoute('GET', '/main/discount', ['IndexController', 'getTodayDiscountRestaurant']);

    // 36.1. 요즘 뜨는 우리동네 음식점 (최근 30일 이내 식당 주문량 기준)
    $r->addRoute('GET', '/main/best-restaurant', ['IndexController', 'getRecentBestRestaurant']);

    // 37.1. 학익동 배달비 무료
    $r->addRoute('GET', '/main/deliver-free', ['IndexController', 'getDeliverFreeRestaurant']);

    // 38.1. 최근 7일 동안 리뷰가 많아요! (최근 7일 이내 작성된 리뷰 개수 기준 정렬)
    $r->addRoute('GET', '/main/review-most', ['IndexController', 'getMostReviewRestaurant']);

    // 39.1. 요기요 플러스 맛집
    $r->addRoute('GET', '/main/yogiyo-plus', ['IndexController', 'getYogiyoPlusRestaurant']);

    // 40.1. 가장 빨리 배달되요 (배달시간 기준 정렬)
    $r->addRoute('GET', '/main/shortest-deliver-time', ['IndexController', 'getFastDeliverRestaurant']);

    // 41.1. 새로 오픈했어요 (개업한 지 7일 이하)
    $r->addRoute('GET', '/main/lastest-restaurant', ['IndexController', 'getNewlyOpenedRestaurant']);

    // 34.2. (비회원)우리동네 찜 많은 음식점 (찜한 수 기준 정렬)
    $r->addRoute('GET', '/non-member/main/favorite-most', ['IndexController', 'getFavoriteMostRestaurantForNonmember']);

    // 35.2. (비회원)학익동 오늘만 할인 (배달할인 or 할인율 > 0)
    $r->addRoute('GET', '/non-member/main/discount', ['IndexController', 'getTodayDiscountRestaurantForNonmember']);

    // 36.2. (비회원)요즘 뜨는 우리동네 음식점 (최근 30일 이내 식당 주문량 기준)
    $r->addRoute('GET', '/non-member/main/best-restaurant', ['IndexController', 'getRecentBestRestaurantForNonmember']);

    // 37.2. (비회원)학익동 배달비 무료
    $r->addRoute('GET', '/non-member/main/deliver-free', ['IndexController', 'getDeliverFreeRestaurantForNonmember']);

    // 38.2. (비회원)최근 7일 동안 리뷰가 많아요! (최근 7일 이내 작성된 리뷰 개수 기준 정렬)
    $r->addRoute('GET', '/non-member/main/review-most', ['IndexController', 'getMostReviewRestaurantForNonmember']);

    // 39.2. (비회원)요기요 플러스 맛집
    $r->addRoute('GET', '/non-member/main/yogiyo-plus', ['IndexController', 'getYogiyoPlusRestaurantForNonmember']);

    // 40.2. (비회원)가장 빨리 배달되요 (배달시간 기준 정렬)
    $r->addRoute('GET', '/non-member/main/shortest-deliver-time', ['IndexController', 'getFastDeliverRestaurantForNonmember']);

    // 41.2. (비회원)새로 오픈했어요 (개업한 지 7일 이하)
    $r->addRoute('GET', '/non-member/main/lastest-restaurant', ['IndexController', 'getNewlyOpenedRestaurantForNonmember']);



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
