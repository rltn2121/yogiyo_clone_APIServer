<?php

require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        /*
         * API No. 1
         * API Name : 찜한 음식점 조회 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "getFavorite":
            http_response_code(200);
            $res->count = getFavoriteCount();
            $res->restaurant = getFavoriteRestaurant();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : 찜 하기 / 취소하기 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "addFavorite":
            $rest_id = $vars['rest_id'];
            http_response_code(200);
            if(!isValidRestaurant($rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isFavoriteExist($rest_id)){
                addFavorite($rest_id); // body(request) 안에 있는 name 받아오기
                $res->message = "찜 목록에 추가되었습니다.";
                $res->code = 101;
            }
            else{
                if(getFavoriteStatus($rest_id)==false){
                    updateFavoriteToTrue($rest_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "찜 목록에 추가되었습니다.";
                    $res->code = 101;
                }
                else{
                    updateFavoriteToFalse($rest_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "찜 목록에서 삭제되었습니다.";
                    $res->code = 102;
                }
            }
            $res->isSuccess = TRUE;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 3
         * API Name : 카테고리별 음식점 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "getRestaurantByCategory":
            http_response_code(200);
            $category = $_GET['category'];
            if(!isValidCategory($category)){
                $res->isSuccess = FALSE;
                $res->code = 207;
                $res->message = "유효하지 않은 카테고리입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->our_village_plus = getOurVillagePlusByCategory($category);
            $res->super_red_week = getSuperRedWeekPlusByCategory($category);
            $res->normal_restaurant = getNormalRestaurantByCategory($category);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 4
         * API Name : 메뉴 검색 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "findMenu":
            http_response_code(200);
            $keyword = $_GET['keyword'];
            $res->result = findMenu($keyword);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 5
         * API Name : 최근 검색어 조회 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "getRecentSearchKeyword":
            http_response_code(200);
            $res->result = getRecentSearchKeyword();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "최근 검색어 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 6
         * API Name : 최근 검색어 삭제 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "deleteRecentSearchKeyword":
            http_response_code(200);
            $idx = $vars['idx'];
            if(!isKeywordIdxExist($idx)){
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "존재하지 않는 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteRecentSearchKeyword($idx);
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "최근 검색어 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 7
         * API Name : 최근 검색어 전체 삭제 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "deleteAllRecentSearchKeyword":
            http_response_code(200);
            deleteAllRecentSearchKeyword();
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "최근 검색어 전체 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8.1
         * API Name : 특정 음식점 메인 조회 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getRestaurantMain":
            http_response_code(200);
            $rest_id = $vars['rest_id'];
            if(!isValidRestaurant($rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->main = getRestaurantMain($rest_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8.2
         * API Name : 특정 음식점 메뉴 조회 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getRestaurantMenu":
            http_response_code(200);
            $rest_id = $vars['rest_id'];
            if(!isValidRestaurant($rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->best_menu = getRestaurantBestMenu($rest_id);
            $res->menu = getRestaurantMenu($rest_id);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8.3
         * API Name : 특정 음식점 리뷰 조회 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getRestaurantReview":
            http_response_code(200);
            $rest_id = $vars['rest_id'];
            if(!isValidRestaurant($rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->review = getRestaurantReview($rest_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8.4
         * API Name : 특정 음식점 정보 조회 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getRestaurantInfo":
            http_response_code(200);
            $rest_id = $vars['rest_id'];
            if(!isValidRestaurant($rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->info = getRestaurantInfo($rest_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 9
         * API Name : 메뉴 추가 옵션 조회 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getMenuOption":
            http_response_code(200);
            $rest_id = $vars['rest_id'];
            $menu_id = $vars['menu_id'];
            if(!isValidMenu($menu_id, $rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->additional_option = getMenuOption($menu_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 10.1
         * API Name : 터치주문내역 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getTouchOrderList":
            http_response_code(200);
            $res->touch_order_count = getTouchOrderCount();
            $res->touch_order_list = getTouchOrderList();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 10.2
         * API Name : 전화주문내역 API
         * 마지막 수정 날짜 : 20.08.20
         */
        case "getCallOrderList":
            http_response_code(200);
            $res->call_order_count = getCallOrderCount();
            $res->call_order_list = getCallOrderList();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 11
         * API Name : 주문 상세 보기 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "getOrderDetail":
            http_response_code(200);
            $order_id = $vars['order_id'];
            if(!isValidOrder($order_id)){
                $res->isSuccess = FALSE;
                $res->code = 206;
                $res->message = "유효하지 않은 주문번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->order_info = getOrderInfo($order_id);
            $res->ordered_menu = getOrderedMenu($order_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 12
         * API Name : 주문표에 메뉴 추가 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "addItemIntoOrderPad":
            http_response_code(200);
            if($req->rest_id == null || $req->menu_id == null  || $req->quantity == null){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "rest_id 또는 menu_id 입력안됨 or quantity == 0";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidRestaurant($req->rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidMenu($req->menu_id, $req->rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($req->option_id != 0 && !isValidOption($req->option_id, $req->menu_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 옵션입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isOrderPadEmpty()){
                if($req->rest_id != getCurrentRestaurantID()){
                    deleteAllItems();
                    $res->message =  "이전에 존재하던 주문표가 모두 삭제되었습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                }
                else{
                    if(isItemExistInTheOrderPad($req->menu_id, $req->option_id)){
                        $res->isSuccess = FALSE;
                        $res->code = 210;
                        $res->message = "이미 추가된 메뉴입니다.";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        break;
                    }
                }
            }

            addItemIntoOrderPad($req->rest_id, $req->menu_id, $req->option_id, $req->quantity);
            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "메뉴가 추가됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 13
         * API Name : 주문표에서 메뉴 삭제 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "deleteItemAtOrderPad":
            http_response_code(200);
            $order_pad_id = $vars['order_pad_id'];
            if(!isValidOrderPadId($order_pad_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 주문표 번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteItemAtOrderPad($order_pad_id); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "메뉴가 삭제됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 14
         * API Name : 주문표 조회 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "getOrderPad":
            http_response_code(200);
            $res->result = getOrderPad();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /* API No. 15
        * API Name : 주문하기 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case "addOrders":
            http_response_code(200);
            if($req->order_id==null || $req->rest_id == null || $req->payment_type == null ||
                $req->order_type == null || $req->menu_id == null || $req->quantity == null){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "정보 누락 or quantity == 0";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isValidOrder($req->order_id)){
                $res->isSuccess = FALSE;
                $res->code = 100;
                $res->message = "주문번호가 이미 존재합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidRestaurant($req->rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "유효하지 않은 식당입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidMenu($req->menu_id, $req->rest_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->option_id != 0 && !isValidOption($req->option_id, $req->menu_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "유효하지 않은 옵션입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $request = "";
            if($req->request == null)
                $request = "요청사항 없음";
            else
                $request = $req->request;

            $user_region_address = getUserLocation();
            $user_location = $user_region_address['region'].' '.$user_region_address['address'];

            addOrders($req->order_id, $req->rest_id, $req->payment_type, $request, $req->order_type, $user_location);

            $order_pad_list = getOrderPad();
            $order_pad_list_count = count($order_pad_list);
            for($i = 0; $i<$order_pad_list_count; $i++){
                addOrderedMenu($req->order_id, $order_pad_list[$i]['menu_id'], $order_pad_list[$i]['quantity'], $order_pad_list[$i]['option_id']);
            }
            deleteAllItems();


            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "주문이 추가됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 16
         * API Name : 재주문하기(주문표에 추가) API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "reOrder":
            http_response_code(200);
            $order_id = $vars['order_id'];
            if(!isValidOrder($order_id)){
                $res->isSuccess = FALSE;
                $res->code = 206;
                $res->message = "유효하지 않은 주문번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $user_region_address = getUserLocation();
            // 1. 배달주소 변경
            updateLocation($user_region_address['region'], $user_region_address['address']);

            // 2. order_id로 주문내역 검색, 주문표에 그대로 추가
            deleteAllItems();
            $reorder_info = getOrderedMenuForReorder($order_id);
            $reorder_info_count = count($reorder_info);
            for($i = 0; $i<$reorder_info_count; $i++){
                addItemIntoOrderPad($reorder_info[$i]['restaurant_id'], $reorder_info[$i]['menu_id'], $reorder_info[$i]['option_id'], $reorder_info[$i]['quantity']);
            }

            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "성공적으로 주문표에 추가됐습니다. 기존에 있던 주문표는 모두 삭제됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 17
         * API Name : 마이 요기요 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "getMyYogiyo":
            http_response_code(200);
            $res->result = getMyYogiyo();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 18
         * API Name : 사용자 정보 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "getUserInfo":
            http_response_code(200);
            $res->result = getUserInfo();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 19
         * API Name : 등록한 카드 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "getCardInfo":
            http_response_code(200);
            $res->result = getCardInfo();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 20
         * API Name : 카드추가 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "addCard":
            http_response_code(200);
            if(strlen($req->card_number) != 16 || !is_numeric($req->card_number)){
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "카드 번호를 확인해주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($req->cvc) != 3 || !is_numeric($req->cvc)){
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "cvc를 확인해주세요.(3자리)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($req->password) != 4 || !is_numeric($req->password)){
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "비밀번호를 확인해주세요.(4자리)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isCardExist($req->card_number)){
                if(!isCardDeleted($req->card_number)){
                    $res->isSuccess = FALSE;
                    $res->code = 210;
                    $res->message = "이미 존재하는 카드입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    updateCardToActive($req->card_number);
                }
            }
            else{
                addCard($req->card_type, $req->card_number, $req->expiration_date,
                    $req->cvc, $req->password, $req->resident_registration_number); // body(request) 안에 있는 name 받아오기
            }

            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "카드 추가 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 21
         * API Name : 카드삭제 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "deleteCard":
            http_response_code(200);
            $card_number = $vars{'card_number'};
            if(!isCardExist($card_number)){
                    $res->isSuccess = FALSE;
                    $res->code = 208;
                    $res->message = "존재하지 않는 카드입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
            }

            else{
                if(isCardDeleted($card_number)){
                    $res->isSuccess = FALSE;
                    $res->code = 211;
                    $res->message = "이미 삭제된 카드입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            updateCardToUnactive($card_number); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "카드 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 22
         * API Name : 결제비밀번호 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updatePaymentPassword":
            http_response_code(200);
            if($req->payment_password != getPaymentPassword()){
                $res->isSuccess = FALSE;
                $res->code = 301;
                $res->message = "기존 비밀번호가 일치하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(strlen($req->payment_password)!=6||strlen($req->new_payment_password) != 6 || !is_numeric($req->new_payment_password)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "비밀번호를 확인해주세요(6자리).";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($req->new_payment_password != $req->check_password){
                $res->isSuccess = FALSE;
                $res->code = 303;
                $res->message = "입력된 비밀번호가 동일하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            updatePaymentPassword($req->new_payment_password); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "비밀번호를 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 23
         * API Name : 휴대전화번호 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updatePhone":
            http_response_code(200);
            if(!is_numeric($req->phone) || strlen($req->phone) != 11){
                $res->isSuccess = FALSE;
                $res->code = 204;
                $res->message = "전화번호 확인 필요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isPhoneExist($req->phone)){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "이미 등록된 전화번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            updatePhone($req->phone); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "휴대전화번호를 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 24
         * API Name : 닉네임 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updateNickname":
            http_response_code(200);
            if(isNicknameExist($req->nickname)){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "이미 존재하는 닉네임입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            updateNickname($req->nickname); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "닉네임을 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 25
         * API Name : 배달 주소 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updateLocation":
            http_response_code(200);

            updateLocation($req->region, $req->address); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "위치 설정이 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 26
         * API Name : 최근 배달위치 조회 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "getRecentLocation":
            http_response_code(200);
            $res->result = getRecentLocation();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "최근 배달위치 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 27
         * API Name : 최근 배달위치 삭제 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "deleteRecentLocation":
            http_response_code(200);
            $idx = $vars['idx'];
            if(!isLocationIdxExist($idx)){
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "존재하지 않는 인덱스입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteRecentLocation($idx);
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "최근 배달위치 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 28
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "addUser":
            http_response_code(200);
            if(isNicknameExist($req->nickname)){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "이미 존재하는 닉네임입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isEmailExist($req->email)){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "이미 등록된 이메일입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!filter_var($req->email, FILTER_VALIDATE_EMAIL) ){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "잘못된 이메일 형식.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }


            출처: https://brtech.tistory.com/36 [Dev Stack]
            if(!is_numeric($req->phone) || strlen($req->phone) != 11){
                $res->isSuccess = FALSE;
                $res->code = 204;
                $res->message = "전화번호 확인 필요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isPhoneExist($req->phone)){
                $res->isSuccess = FALSE;
                $res->code = 205;
                $res->message = "이미 등록된 전화번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(strlen($req->payment_password)!=6||!is_numeric($req->payment_password)){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "결제비밀번호를 확인해주세요(6자리).";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            addUser($req->nickname, $req->email, $req->password, $req->phone, $req->region, $req->address, $req->payment_password); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 29
         * API Name : 회원탈퇴 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "deleteUser":
            http_response_code(200);
            $user_id = $vars['user_id'];
            if(!isValidUser($user_id)){
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "존재하지 않는 사용자입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteUser($user_id); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "회원탈퇴가 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 30
         * API Name : 리뷰 작성하기 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "addReview":
            http_response_code(200);
            if(!isValidOrder($req->order_id)){
                $res->isSuccess = FALSE;
                $res->code = 206;
                $res->message = "유효하지 않은 주문번호입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isReviewExist($req->order_id)){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "이미 리뷰를 작성했습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            addReview($req->order_id, $req->contents, $req->taste_score, $req->quantity_score, $req->delivery_score); // body(request) 안에 있는 name 받아오기
            $image_count = count($req->image);
            if($image_count > 0){
                $review_id = getReviewId($req->order_id);
                for($i = 0; $i<$image_count; $i++){
                    addReviewImage($review_id, $req->image[$i]->image_url);
                }
            }
            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "리뷰 작성이 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 31
         * API Name : 리뷰 삭제 API
         * 마지막 수정 날짜 : 20.08.21
         */
        case "deleteReview":
            http_response_code(200);
            $review_id = $vars['review_id'];
            if(!isValidReview($review_id)){
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "존재하지 않는 리뷰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            deleteReview($review_id);
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "리뷰 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 32
        * API Name : 리뷰 추천 / 취소하기 API
        * 마지막 수정 날짜 : 20.08.18
        */
        case "addReviewLike":
            $review_id = $vars['review_id'];
            http_response_code(200);


            if(!isReviewLikeExist($review_id)){
                addReviewLike($review_id); // body(request) 안에 있는 name 받아오기
                $res->message = "리뷰를 추천했습니다.";
                $res->code = 101;
            }
            else{
                if(getReviewLikeStatus($review_id)==false){
                    updateReviewLikeToTrue($review_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "리뷰를 추천했습니다.";
                    $res->code = 101;
                }
                else{
                    updateReviewLikeToFalse($review_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "리뷰 추천을 취소했습니다.";
                    $res->code = 102;
                }
            }
            $res->isSuccess = TRUE;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 33
        * API Name : 리뷰 신고 API
        * 마지막 수정 날짜 : 20.08.21
        */
        case "reportReview":
            http_response_code(200);
            $review_id = $vars['review_id'];
            if(!isValidReview($review_id)){
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "존재하지 않는 리뷰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isReviewAlreadyReport($review_id)){
                $res->isSuccess = FALSE;
                $res->code = 208;
                $res->message = "이미 신고한 리뷰입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            reportReview($review_id);
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "리뷰 신고 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    echo "sql 오류";
    return getSQLErrorException($errorLogs, $e, $req);

    return 0;
}
