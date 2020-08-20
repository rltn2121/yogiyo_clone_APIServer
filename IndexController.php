<?php

require 'function.php';

const JWT_SECRET_KEY = "";

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
         * 마지막 수정 날짜 : 20.08.18
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
                $res->result = addFavorite($rest_id); // body(request) 안에 있는 name 받아오기
                $res->message = "찜 목록에 추가되었습니다.";
                $res->code = 101;
            }
            else{
                if(getFavoriteStatus($rest_id)==false){
                    $res->result = updateFavoriteToTrue($rest_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "찜 목록에 추가되었습니다.";
                    $res->code = 101;
                }
                else{
                    $res->result = updateFavoriteToFalse($rest_id); // body(request) 안에 있는 name 받아오기
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
         * 마지막 수정 날짜 : 20.08.16
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
         * API Name : 음식점 정보 조회 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "getRestaurantDetail":
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
            $res->best_menu = getRestaurantBestMenu($rest_id);
            $res->menu = getRestaurantMenu($rest_id);
            $res->review = getRestaurantReview($rest_id);
            $res->info = getRestaurantInfo($rest_id);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 6
         * API Name : 메뉴 추가 옵션 조회 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "getMenuOption":
            http_response_code(200);
            $menu_id = $vars['menu_id'];
            if(!isValidMenu($menu_id)){
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
         * API No. 7
         * API Name : 주문내역 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "getOrderList":
            http_response_code(200);
            $res->touch_order_count = getTouchOrderCount();
            $res->call_order_count = getCallOrderCount();
            $res->touch_order_list = getTouchOrderList();
            $res->call_order_list = getCallOrderList();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8
         * API Name : 주문 상세 보기 API
         * 마지막 수정 날짜 : 20.08.18
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
       * API No. 9
       * API Name : 주문표에 메뉴 추가 API
       * 마지막 수정 날짜 : 20.08.18
       */
        case "addItemIntoOrderPad":
            http_response_code(200);
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

            $res->result = addItemIntoOrderPad($req->rest_id, $req->menu_id, $req->option_id, $req->quantity);
            $res->code = 101;
            $res->message = "메뉴가 추가됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        API No. 10
        * API Name : 주문표에서 메뉴 삭제 API
        * 마지막 수정 날짜 : 20.08.18
        */
        case "deleteItemAtOrderPad":
            http_response_code(200);
            if(!isItemExistInTheOrderPad($req->menu_id, $req->option_id)){
                $res->isSuccess = FALSE;
                $res->code = 203;
                $res->message = "존재하지 않는 메뉴입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = deleteItemAtOrderPad($req->menu_id, $req->option_id); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "메뉴가 삭제됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 11
         * API Name : 주문표 가져오기 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "getOrderPad":
            http_response_code(200);

            $res->result = getOrderPad();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "조회 성공.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /* API No. 12
        * API Name : 주문하기 API
        * 마지막 수정 날짜 : 20.08.18
        */
        case "addOrders":
            http_response_code(200);
            $res->addOrders = addOrders($req->order_id, $req->rest_id, $req->payment_type, $req->request, $req->order_type);
            $res->addOrderedMenu = addOrderedMenu($req->order_id, $req->menu_id, $req->quantity);
            $res->addOrderedOption = addOrderedOption($req->order_id, $req->menu_id, $req->option_id);

            $res->code = 101;
            $res->message = "주문이 추가됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 13
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
         * API No. 14
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
         * API No. 15
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
         * API No. 16
         * API Name : 카드추가 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "addCard":
            http_response_code(200);
            if(isCardExist($req->card_number)){
                if(!isCardDeleted($req->card_number)){
                    $res->isSuccess = FALSE;
                    $res->code = 210;
                    $res->message = "이미 존재하는 카드입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                else{
                    $res->result = updateCardToActive($req->card_number);
                }
            }
            else{
                $res->result = addCard($req->card_type, $req->card_number, $req->expiration_date,
                    $req->cvc, $req->password, $req->resident_registration_number); // body(request) 안에 있는 name 받아오기
            }

            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "카드 추가 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 17
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
            $res->result = updateCardToUnactive($card_number); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "카드 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 18
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

            if($req->new_payment_password != $req->check_password){
                $res->isSuccess = FALSE;
                $res->code = 302;
                $res->message = "입력된 비밀번호가 동일하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = updatePaymentPassword($req->new_payment_password); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "비밀번호를 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 19
         * API Name : 휴대전화번호 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updatePhone":
            http_response_code(200);

            $res->result = updatePhone($req->phone); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "휴대전화번호를 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 20
         * API Name : 닉네임 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updateNickname":
            http_response_code(200);

            $res->result = updateNickname($req->nickname); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "닉네임을 변경했습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 21
         * API Name : 회원탈퇴 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "deleteUser":
            http_response_code(200);

            $res->result = deleteUser(); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 102;
            $res->message = "회원탈퇴가 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 22
         * API Name : 배달 주소 변경 API
         * 마지막 수정 날짜 : 20.08.18
         */
        case "updateLocation":
            http_response_code(200);

            $res->result = updateLocation($req->region, $req->address); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 103;
            $res->message = "위치 설정이 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 23
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 20.08.16
         */
        case "addUser":
            http_response_code(200);
            $res->result = addUser($req->nickname, $req->email, $req->password, $req->phone, $req->region, $req->address, $req->payment_password); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 24
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
            //echo print_r($req->image);
            $res->result = addReview($req->order_id, $req->contents, $req->taste_score, $req->quantity_score, $req->delivery_score, $req->image); // body(request) 안에 있는 name 받아오기
            $res->isSuccess = TRUE;
            $res->code = 101;
            $res->message = "리뷰 작성이 완료됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 25
        * API Name : 리뷰 추천 / 취소하기 API
        * 마지막 수정 날짜 : 20.08.18
        */
        case "addReviewLike":
            $review_id = $vars['review_id'];
            http_response_code(200);


            if(!isReviewLikeExist($review_id)){
                $res->result = addReviewLike($review_id); // body(request) 안에 있는 name 받아오기
                $res->message = "리뷰를 추천했습니다.";
                $res->code = 101;
            }
            else{
                if(getReviewLikeStatus($review_id)==false){
                    $res->result = updateReviewLikeToTrue($review_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "리뷰를 추천했습니다.";
                    $res->code = 101;
                }
                else{
                    $res->result = updateReviewLikeToFalse($review_id); // body(request) 안에 있는 name 받아오기
                    $res->message = "리뷰 추천을 취소했습니다.";
                    $res->code = 102;
                }
            }
            $res->isSuccess = TRUE;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
