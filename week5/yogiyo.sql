-- yogiyo database
-- user_id: 1xxxxxxx
-- restaurant_id: 2xxxxxxx
-- menu_id: 3xxxxxxx
-- review_id: 4xxxxxxx
-- additional_option: 5xxxxxxx

-- 1. 찜한 음식점
-- 1.1. 찜한 음식점 개수
select count(*) as total_count
from favorite_restaurant
where user_id = ?;

-- 1.2. 찜한 음식점 정보
select restaurant_name,
       restaurant.restaurant_id,
       round(avg((taste_score + quantity_score + delivery_score) / 3), 1) as score,
       count(review_id)                                                   as review_num,
       count(review_owner_comment.contents)                               as owner_comment_num,
       concat(restaurant.delivery_discount, '원')                          as delivery_discount,
       best_menu,
       is_deliver,
       image_url
from (favorite_restaurant
    left outer join (restaurant left outer join (orders left outer join (review left outer join review_owner_comment using (review_id)) using (order_id)) using (restaurant_id))
    using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as t using (restaurant_id)
where favorite_restaurant.user_id = ?
  and status = 1
group by favorite_restaurant.restaurant_id;


-- 1.3. 사용자 아이디 == '10000001' && 찜한 음식점에 속한 식당의 메뉴별 판매량 (식당별 상위 2개 메뉴 추출해야함)
select restaurant_id, menu_name, sales
from menu
         left outer join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t using (menu_id)
where restaurant_id in (
    select restaurant_id
    from favorite_restaurant
    where user_id = '10000001'
)
order by restaurant_id, sales desc;

-- 2. 사용자 지역 == 식당 지역 && 우리동네 플러스 && 슈퍼 레드 위크
-- 전체보기(우리동네플러스)
select restaurant_name,
       restaurant_id,
       restaurant.region,
       round(avg((taste_score + quantity_score + delivery_score) / 3), 1) as score,
       count(review_id)                                                   as review_num,
       count(review_owner_comment.contents)                               as owner_comment_num,
       concat(restaurant.delivery_discount, '원')                          as delivery_discount,
       best_menu,
       discount_rate,
       is_best_restaurant,
       is_cesco,
       datediff(now(), restaurant.created_at) as sales_days,
       estimated_delivery_time,
       image_url,
       is_deliver
from
    (restaurant left outer join (orders left outer join (review left outer join review_owner_comment using (review_id)) using (order_id)) using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as t using (restaurant_id)
where restaurant.our_village_plus = 'Y'
-- where restaurant.super_red_week = 'Y'
-- where restaurant.our_village_plus = 'N' and restaurant.super_red_week = 'N'
  and restaurant.type = ?
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant.restaurant_id;



-- 2.4. (2.3.)에 속하는 가게들의 메뉴별 판매량
select restaurant_id, menu_name, sales
from menu
         left outer join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t using (menu_id)
where restaurant_id in (
    select restaurant_id
    from super_red_week
             join restaurant using (restaurant_id)
    where red_week_status = 'Y'
      and restaurant.type = '치킨'
)
order by restaurant_id, sales desc;



-- 4. 후참잘 선택화면
select restaurant_name,
       score,
       avg_taste,
       avg_quantity,
       avg_delivery,
       concat(delivery_discount, '원')         as delivery_discount,
       estimated_delivery_time,
       concat(minimum_deliverable_price, '원') as minimum_deliverable_price,
       payment_type,
       concat(delivery_price, '원')            as delivery_price,
       owner_notice,
       favorite_num,
       menu_num,
       review_num,
       owner_comment_num,
       background_url,
       share_url
from restaurant
         left outer join (
    select *
    from (select restaurant_id, count(*) as menu_num, favorite_num
          from menu
                   left outer join (
              select restaurant_id, count(*) as favorite_num
              from favorite_restaurant
              group by restaurant_id
          ) as t using (restaurant_id)
          group by restaurant_id) as t1
             left outer join (select restaurant_id,
                                     round(avg((taste_score + quantity_score + delivery_score) / 3), 1) as score,
                                     round(avg(taste_score), 1)                                         as avg_taste,
                                     round(avg(quantity_score), 1)                                      as avg_quantity,
                                     round(avg(delivery_score), 1)                                      as avg_delivery,
                                     count(*)                                                           as review_num,
                                     count(review_owner_comment.contents)                               as owner_comment_num
                              from (review
                                  left outer join orders using (order_id))
                                       left outer join review_owner_comment using (review_id)
                              group by restaurant_id) as t2 using (restaurant_id)
) as temp using (restaurant_id)
where restaurant_id = ?;

-- 식당 정보
select group_concat(owner_notice_image.image_url separator ';') as owner_notice_image,
       owner_notice,
       business_hours,
       concat(phone, '(요기요 제공 번호)')                             as phone,
       concat(region, ' ', address)                             as location,
       is_cesco,
       concat(minimum_deliverable_price, '원')                   as min_deliver_price,
       payment_type,
       restaurant_name,
       business_registration_number,
       origin_info
from restaurant
         join owner_notice_image using (restaurant_id)
where restaurant_id = ?;

-- 5. 후참잘 메뉴
-- 5.1. 인기 메뉴 (판매량 상위 2개)
select menu_name, menu_id, concat(price, '원') as price, image_url, sales
from menu
         natural join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t
where restaurant_id = ?
order by restaurant_id, sales desc
limit 2;

-- 5.2. 카테고리별 메뉴
select category, menu_name, menu_id, concat(price, '원') as price, image_url
from menu
where restaurant_id = ?;

-- 6. 후참잘 추가옵션
select menu_name, option_type, option_id, option_name, concat(extra_charge, '원') as extra_charge, share_url
from additional_option
         join menu using (menu_id)
where menu_id = ?
order by option_type;

-- 7. 리뷰
select review_id,
       menu.restaurant_id,
       nickname,
       profile_image_url.image_url,
       group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info,
       group_concat(distinct review_image.image_url separator ';')           as review_image_url,
       case
           when timestampdiff(hour, review.created_at, now()) < 1
               then concat(timestampdiff(minute, review.created_at, now()), '분전')
           when timestampdiff(day, review.created_at, now()) < 1
               then concat(timestampdiff(hour, review.created_at, now()), '시간전')
           when timestampdiff(day, review.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review.created_at, now()) < 7
               then concat(timestampdiff(day, review.created_at, now()), '일전')
           else date_format(review.created_at, '%Y.%m.%d %H:%i')
           end                                                               as review_submit_time,
       round((taste_score + quantity_score + delivery_score) / 3, 1)         as score,
       taste_score,
       quantity_score,
       delivery_score,
       review.contents                                                       as review_contents,
       review_owner_comment.contents                                         as reply_contents,
       case
           when timestampdiff(hour, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(minute, review_owner_comment.created_at, now()), '분전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(hour, review_owner_comment.created_at, now()), '시간전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review_owner_comment.created_at, now()) < 7 then concat(
                   timestampdiff(day, review_owner_comment.created_at, now()), '일전')
           else date_format(review_owner_comment.created_at, '%Y.%m.%d %H:%i')
           end                                                               as review_submit_time
from ((((((review
    left outer join orders using (order_id))
    left outer join review_owner_comment using (review_id))
    left outer join users using (user_id))
    left outer join profile_image_url using (type))
    left outer join ordered_menu using (order_id))
    left outer join menu using (menu_id))
         left outer join review_image using (review_id)
where menu.restaurant_id = ?
group by review_id;

select *
from review_like;

-- 8.1. 터치주문 수
select count(*) as touch_num
from orders
where user_id = '10000001'
  and order_type = 'touch';

-- 8.2. 전화주문 수
select count(*) as call_num
from orders
where user_id = ?
  and order_type = 'call';

-- 8.3. 주문번호별 가게 정보
select order_id,
       order_type,
       date_format(orders.created_at, '%Y.%m.%d %H:%i')                      as order_date,
       delivery_status,
       restaurant_name,
       restaurant.image_url,
       delivery_status,
       group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info
from ((orders left outer join ordered_menu using (order_id) ) left outer join restaurant using (restaurant_id))
         left outer join menu using (menu_id)
where user_id = '10000001'
  and order_type = 'touch'
group by order_id;


-- 9. 주문 상세보기
-- 9.1. 주문정보

select restaurant_name,
       delivery_status,
       order_id,
       date_format(orders.created_at, '%Y.%m.%d %H:%i')              as order_date,
       concat((select sum(price + IFNULL(extra_charge, 0))
               from (select ordered_menu.price,
                            ordered_menu.extra_charge
                     from (ordered_menu left outer join menu using (menu_id))
                              left outer join additional_option using (option_id)
                     where ordered_menu.order_id = ?) as temp), '원') as sum_menu_price,
       concat(orders.delivery_price, '원')                            as delivery_price,
       concat(orders.delivery_discount, '원')                         as delivery_discount,
       concat(((select sum(price + IFNULL(extra_charge, 0))
                from (select ordered_menu.price,
                             ordered_menu.extra_charge
                      from (ordered_menu left outer join menu using (menu_id))
                               left outer join additional_option using (option_id)
                      where ordered_menu.order_id = ?) as temp) + orders.delivery_price - orders.delivery_discount),
              '원')                                                   as actual_price,
       orders.payment_type,
       concat('','0',users.phone,'') as phone,
       concat(users.region, ' ', users.address)                      as user_location,
       request
from (orders join restaurant using (restaurant_id))
         join users using (user_id)
where orders.order_id = ?;




select *
from orders
where order_id = '200614-20-144784';

select *
from ordered_menu
where order_id='200619-20-156842';


-- 주문한 메뉴 보기
select ordered_menu.order_id, menu_name, quantity, ordered_menu.price, option_name, ordered_menu.extra_charge
from (ordered_menu left outer join menu using (menu_id))
         left outer join additional_option using (option_id)
where ordered_menu.order_id = '200619-20-156842';



-- 10.1. 이용내역
-- 쿠폰개수, 포인트 구현 안함
select nickname, type, image_url, review_num
from (users join profile_image_url using (type))
         join (select user_id, count(*) as review_num
               from review
                        left outer join orders using (order_id)
               group by user_id) as t using (user_id)
where user_id = '10000001';

-- 10.2. 개인정보
select email, phone, nickname
from users
where user_id = '10000001';

-- 10.3. 등록한 카드
select *
from registered_card
where user_id = '10000001';


-- 메뉴명으로 검색
select restaurant_id,
       restaurant_name,
       restaurant.image_url,
       datediff(now(), restaurant.created_at)                                        as sales_days,
       restaurant.is_best_restaurant,
       restaurant.is_cesco,
       estimated_delivery_time,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(distinct review_id)                                                     as review_num,
       count(distinct review_owner_comment.contents)                                 as owner_comment_num,
       group_concat(menu_name separator ',')                                         as searched_menu
from (((restaurant join menu using (restaurant_id)) left outer join orders using (restaurant_id)) left outer join review using (order_id))
         left outer join review_owner_comment using (review_id)
where menu_name like concat('%', ?, '%')
group by restaurant_id;



insert into users (nickname, email, password, phone, region, address)
values (?, ?, ?, ?, ?, ?);


-- 주문표에 추가하기
insert into order_pad (restaurant_id, menu_id, quantity, option_id)
values (?, ?, ?, ?)

-- 식당 변경하기


-- 식당 찜하기(추가)
insert into favorite_restaurant
    (user_id, restaurant_id)
values (10000001, ?);

-- 찜 목록 존재 여부
SELECT EXISTS(select * FROM favorite_restaurant WHERE user_id = 10000001 and restaurant_id = ?) as exist;

-- 식당 찜하기(변경)
update favorite_restaurant
set status='N'
where user_id = 10000001
  and restaurant_id = ?;

-- 찜 목록 상태
select status
from favorite_restaurant
where user_id = '10000001'
  and restaurant_id = ?;

-- 카드 존재 여부
SELECT EXISTS(select * FROM registered_card WHERE card_number = ?) as exist;

-- 카드 삭제
insert into registered_card
values ('10000001', ?, ?, ?, ?, ?, ?, '0');

-- 결제 비밀번호 조회
select payment_password
from users
where user_id = 10000001;

-- 결제 비밀번호 변경
update users
set payment_password = ?
where user_id = 10000001;

-- 휴대전화번호 변경
update users
set phone = ?
where user_id = 10000001;

-- 닉네임 변경
update users
set nickname = ?
where user_id = 10000001;

-- 주문표 메뉴 삭제
delete
from order_pad
where order_pad_id = ?;

insert into order_pad (restaurant_id, menu_id, option_id, quantity)
values ('20000001', '30000002', '50000002', '3');

SELECT EXISTS(select * FROM order_pad WHERE menu_id = ? and option_id = ?) as exist;

-- 주문표 비우기
truncate order_pad;

-- 현재 주문표의 식당ID 가져오기
select restaurant_id
from order_pad
limit 1;

SELECT NOT EXISTS(select * FROM order_pad) as exist;

select price
from menu
where menu_id = ?;
select extra_charge
from additional_option
where option_id = ?;

insert into orders (order_id, restaurant_id, user_id, payment_type, request, order_type, delivery_price,
                    delivery_discount)
values (?, ?, '10000001', ?, ?, ?, ?, ?);



SELECT EXISTS(select * FROM additional_option WHERE option_id = ? and menu_id = ?) as exist;

-- 주소변경
update users
set region = ? and address = ?
where user_id = 10000002;

-- 주문한 메뉴 조회(재주문용)
select restaurant_id, menu_id, option_id, quantity
from ordered_menu
         join orders using (order_id)
where order_id = '200619-20-156842';


CREATE TABLE recent_user_location
(
    `idx`        INT         NOT NULL AUTO_INCREMENT,
    `user_id`    INT         NOT NULL,
    `region`     VARCHAR(45) NOT NULL,
    `address`    VARCHAR(45) NOT NULL,
    `created_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (idx, user_id, region, address)
) default character set utf8
  collate utf8_general_ci;


insert into recent_user_location (user_id, region, address)
values (10000001, ?, ?);


select concat(region, ' ', address) as location
from recent_user_location
where user_id = 10000001
order by created_at;



insert into review_report (user_id, review_id)
values (10000001, ?);

SELECT NOT EXISTS(select * FROM order_pad where user_id = ?) as exist;

update recent_search_keyword
set updated_at = now()
where user_id = ?
  and keyword = ?;

select concat(menu_name, ' x ', quantity)                              as menu_list,
       ordered_menu.price,
       concat(' - ', additional_option.option_type, ': ', option_name) as option_list,
       ordered_menu.extra_charge
from (ordered_menu left outer join menu using (menu_id))
         left outer join additional_option using (option_id)
where ordered_menu.order_id = ?

select restaurant_id, count(*) as fav_cnt
from favorite_restaurant
group by restaurant_id
order by fav_cnt desc
limit 20;


select restaurant_id,
       round(avg((taste_score + quantity_score + delivery_score) / 3), 1) as score,
       count(*)                                                           as review_num
from (review
         left outer join orders using (order_id))
group by restaurant_id;

-- 1. 우리동네 찜 많은 음식점 (찜한 수 기준 정렬)
select restaurant.restaurant_id,
       restaurant_name,
       image_url,
       best_menu,
       ifnull(fav_cnt, 0)    as fav_cnt,
       ifnull(score, 0)      as score,
       ifnull(review_num, 0) as review_num
from ((restaurant
    left outer join (select restaurant_id, count(*) as fav_cnt
                     from favorite_restaurant
                     group by restaurant_id
    ) as temp using (restaurant_id))
    left outer join (select restaurant_id,
                            round(avg((taste_score + quantity_score + delivery_score) / 3), 1) as score,
                            count(*)                                                           as review_num
                     from (review
                              left outer join orders using (order_id))
                     group by restaurant_id) as temp2 using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as grouped_menu using (restaurant_id)
where restaurant.region = (select region from users where user_id = ?)
  and is_deliver = 'Y'
order by fav_cnt desc
limit 20;


-- 2.1 학익동 오늘만 할인(할인율) (할인률 > 0)
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       concat(round(discount_rate * 100, 0), '% 할인')                                 as discount_rate,
       if(restaurant.delivery_price = 0, '배달비 무료', ' ')                              as is_delivery_free,
       image_url
from restaurant
         left outer join (orders left outer join review using (order_id)) using (restaurant_id)
where restaurant.discount_rate > 0
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant_id;

-- 2.2 학익동 오늘만 할인(배달할인) (배달 할인 > 0)
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       concat(restaurant.delivery_discount, '원 할인')                                  as delivery_discount,
       if(restaurant.delivery_price = 0, '배달비 무료', ' ')                              as is_delivery_free,
       image_url
from restaurant
         left outer join (orders left outer join review using (order_id)) using (restaurant_id)
where restaurant.delivery_discount > 0
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant_id;

-- 3. 요즘 뜨는 우리동네 음식점 (최근 30일 이내 식당 주문량 기준)
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       best_menu,
       image_url
from (restaurant left outer join (orders left outer join review using (order_id)) using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as grouped_menu using (restaurant_id)
where timestampdiff(day, restaurant.created_at, now()) <= 30
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant_id
order by count(order_id) desc;

-- 4. 학익동 배달비 무료
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       if(restaurant.delivery_price = 0, '배달비 무료', ' ')                              as is_delivery_free,
       concat('최소주문 ', format(restaurant.minimum_deliverable_price, 0), '원')         as min_deliver_price,
       image_url
from restaurant
         left outer join (orders left outer join review using (order_id)) using (restaurant_id)
where restaurant.delivery_price = 0
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant_id;


-- 5. 최근 7일 동안 리뷰가 많아요! (최근 7일 이내 작성된 리뷰 개수 기준 정렬)
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       best_menu,
       image_url
from ((restaurant left outer join (orders left outer join review using (order_id)) using (restaurant_id))
    left outer join (select restaurant_id, count(*) as last_7days_review_cnt
                     from (orders left outer join review using (order_id))
                              left outer join restaurant using (restaurant_id)
                     where timestampdiff(day, review.created_at, now()) <= 7
                     group by restaurant_id) as temp using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as grouped_menu using (restaurant_id)
where restaurant.region = (select region from users where user_id = ?)
group by restaurant_id
order by last_7days_review_cnt desc;

-- 6. 요기요 플러스 맛집
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       best_menu,
       image_url
from (restaurant left outer join (orders left outer join review using (order_id)) using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as grouped_menu using (restaurant_id)
where restaurant.is_yogiyo_plus = 'Y'
  and restaurant.region = (select region from users where user_id = ?)
group by restaurant_id;

--  7. 가장 빨리 배달되요 (배달시간 기준 정렬)
select restaurant_id,
       restaurant_name,
       ifnull(round(avg((taste_score + quantity_score + delivery_score) / 3), 1), 0) as score,
       count(review_id)                                                              as review_num,
       estimated_delivery_time,
       best_menu,
       image_url
from (restaurant left outer join (orders left outer join review using (order_id)) using (restaurant_id))
         left outer join (select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
                          from (select orders.restaurant_id, menu_name, sum(quantity) as sales
                                from orders
                                         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
                                group by restaurant_id, menu_name) as temp
                          group by restaurant_id) as grouped_menu using (restaurant_id)
where restaurant.region = (select region from users where user_id = ?)
group by restaurant_id
order by restaurant.estimated_delivery_time;


-- 8. 새로 오픈했어요 (개업한 지 7일 이하)
select restaurant_id, restaurant_name, image_url
from restaurant
where timestampdiff(day, restaurant.created_at, now()) <= 7
  and restaurant.region = (select region from users where user_id = ?);


-- restaurant_id 기준 menu_id group_concat
select restaurant_id, group_concat(temp.menu_name order by temp.sales desc) as best_menu
from (select orders.restaurant_id, menu_name, sum(quantity) as sales
      from orders
               left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
      group by restaurant_id, menu_name) as temp
group by restaurant_id;


-- 식당별 메뉴 판메량
select orders.restaurant_id, menu_name, sum(quantity) as sales
from orders
         left outer join (ordered_menu left outer join menu using (menu_id)) using (order_id)
group by restaurant_id, menu_id



-- 추천한 리뷰
select *
from review_like
where user_id = '10000001';

select restaurant_id,
       review_id,
       users.nickname,
       profile_image_url.image_url                                   as profile_image,
       group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info,
       group_concat(distinct review_image.image_url separator '; ')           as review_image,
       round((taste_score + quantity_score + delivery_score) / 3, 1) as score,
       taste_score,
       quantity_score,
       delivery_score,
       ifnull(status,0) as review_like_status,
        case
           when timestampdiff(hour, review.created_at, now()) < 1
               then concat(timestampdiff(minute, review.created_at, now()), '분전')
           when timestampdiff(day, review.created_at, now()) < 1
               then concat(timestampdiff(hour, review.created_at, now()), '시간전')
           when timestampdiff(day, review.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review.created_at, now()) < 7
               then concat(timestampdiff(day, review.created_at, now()), '일전')
           else date_format(review.created_at, '%Y.%m.%d %H:%i')
           end                                                               as review_submit_time,
        review.contents                                               as review_contents,
        case
           when timestampdiff(hour, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(minute, review_owner_comment.created_at, now()), '분전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(hour, review_owner_comment.created_at, now()), '시간전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review_owner_comment.created_at, now()) < 7 then concat(
                   timestampdiff(day, review_owner_comment.created_at, now()), '일전')
           else date_format(review_owner_comment.created_at, '%Y.%m.%d %H:%i')
           end                                                               as reply_submit_time,
        review_owner_comment.contents                                 as reply_contents
from ((((((review left outer join review_owner_comment using (review_id))
    left outer join review_image using (review_id))
    left outer join (orders left outer join (ordered_menu left outer join (select menu_id, menu_name from menu) as t using (menu_id)) using (order_id)) using (order_id))
    left outer join restaurant using (restaurant_id))
         left outer join (users left outer join profile_image_url using (type)) using (user_id))) left outer join (select review_id, status
from review_like
where user_id='10000001') as s using (review_id)
where restaurant_id = '20000006'
group by review_id;

select review_id, status
from review_like
where user_id='10000001';







-- 작성한 리뷰




select review_id,
       menu.restaurant_id,
       nickname,
       profile_image_url.image_url,
       group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info,
       group_concat(distinct review_image.image_url separator ';')           as review_image_url,
       case
           when timestampdiff(hour, review.created_at, now()) < 1
               then concat(timestampdiff(minute, review.created_at, now()), '분전')
           when timestampdiff(day, review.created_at, now()) < 1
               then concat(timestampdiff(hour, review.created_at, now()), '시간전')
           when timestampdiff(day, review.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review.created_at, now()) < 7
               then concat(timestampdiff(day, review.created_at, now()), '일전')
           else date_format(review.created_at, '%Y.%m.%d %H:%i')
           end                                                               as review_submit_time,
       round((taste_score + quantity_score + delivery_score) / 3, 1)         as score,
       taste_score,
       quantity_score,
       delivery_score,
       review.contents                                                       as review_contents,
       review_owner_comment.contents                                         as reply_contents,
       case
           when timestampdiff(hour, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(minute, review_owner_comment.created_at, now()), '분전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 1 then concat(
                   timestampdiff(hour, review_owner_comment.created_at, now()), '시간전')
           when timestampdiff(day, review_owner_comment.created_at, now()) < 2 then '어제'
           when timestampdiff(day, review_owner_comment.created_at, now()) < 7 then concat(
                   timestampdiff(day, review_owner_comment.created_at, now()), '일전')
           else date_format(review_owner_comment.created_at, '%Y.%m.%d %H:%i')
           end                                                               as review_submit_time
from ((((((review
    left outer join orders using (order_id))
    left outer join review_owner_comment using (review_id))
    left outer join users using (user_id))
    left outer join profile_image_url using (type))
    left outer join ordered_menu using (order_id))
    left outer join menu using (menu_id))
         left outer join review_image using (review_id)
where menu.restaurant_id = ?
group by review_id;