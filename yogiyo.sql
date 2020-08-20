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
where user_id=?;

-- 1.2. 찜한 음식점 정보
select restaurant_name,
       restaurant.restaurant_id,
       TB.score,
       TB.review_num,
       TB.owner_comment_num,
       concat(delivery_discount,'원') as delivery_discount,
       is_deliver,
       image_url
from (favorite_restaurant left outer join restaurant using (restaurant_id))
left outer join (select restaurant_id,
                      round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score,
                      count(*)                                                 as review_num,
                      count(review_owner_comment.contents)                     as owner_comment_num
               from (review
                   left outer join orders using (order_id))
                        left outer join review_owner_comment using (review_id)
               group by restaurant_id) as TB on TB.restaurant_id = restaurant.restaurant_id
where user_id='10000001'
and status=1;



-- 1.3. 사용자 아이디 == '10000001' && 찜한 음식점에 속한 식당의 메뉴별 판매량 (식당별 상위 2개 메뉴 추출해야함)
select restaurant_id, menu_name, sales
from menu
         left outer join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t using (menu_id)
where restaurant_id in (
    select restaurant_id from favorite_restaurant
    where user_id='10000001'
    )
order by restaurant_id, sales desc;

-- 2. 사용자 지역 == 식당 지역 && 우리동네 플러스 && 슈퍼 레드 위크
-- 전체보기(우리동네플러스)
select our_village_plus,restaurant_name,restaurant_id,restaurant.region,score,review_num,t.owner_comment_num,concat(delivery_discount,'원') as delivery_discount,
       discount_rate,is_best_restaurant,is_cesco,datediff(now(), created_at) as sales_days,estimated_delivery_time,image_url,is_deliver
from restaurant
         left outer join (
    select restaurant_id,
           round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score,
           count(*)                                                 as review_num,
           count(review_owner_comment.contents)                     as owner_comment_num
    from (review
        left outer join orders using (order_id))
             left outer join review_owner_comment using (review_id)
    group by restaurant_id
) as t using (restaurant_id)
where restaurant.our_village_plus = 'Y'
  and is_portion='Y'
  and restaurant.region = (select region from users where user_id = ?);

-- 전체보기(슈퍼레드위크)
select super_red_week,restaurant_name,restaurant_id,restaurant.region,score,review_num,t.owner_comment_num,concat(delivery_discount,'원') as delivery_discount,
       discount_rate,is_best_restaurant,is_cesco,datediff(now(), created_at) as sales_days,estimated_delivery_time,image_url,is_deliver
from restaurant
         left outer join (
   select restaurant_id, round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score, count(*) as review_num, count(review_owner_comment.contents) as owner_comment_num
from (review
         left outer join orders using (order_id)) left outer join review_owner_comment using (review_id)
group by restaurant_id
) as t using (restaurant_id)
where super_red_week = 'Y'
  and is_portion='Y'
  and restaurant.region = (select region from users where user_id=?);

-- 전체보기(일반음식점)
select restaurant_name,restaurant_id,restaurant.region,score,review_num,t.owner_comment_num,concat(delivery_discount,'원') as delivery_discount,discount_rate,       is_best_restaurant,
       is_cesco,datediff(now(), created_at) as sales_days,estimated_delivery_time,image_url,is_deliver
from restaurant
         left outer join (
    select restaurant_id,
           round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score,
           count(*)                                                 as review_num,
           count(review_owner_comment.contents)                     as owner_comment_num
    from (review
        left outer join orders using (order_id))
             left outer join review_owner_comment using (review_id)
    group by restaurant_id
) as t using (restaurant_id)
where super_red_week = 'N'
  and our_village_plus = 'N'
  and is_portion='Y'
  and restaurant.region = (select region from users where user_id = ?);



-- 2.1. 사용자 지역 == 식당 지역 && 우리동네플러스 && 식당 분류가 '치킨'인 식당 정보
select our_village_plus,
       restaurant_name,
       restaurant_id,
       restaurant.region,
       score,
       review_num,
       t.owner_comment_num,
       concat(delivery_discount,'원') as delivery_discount,
       discount_rate,
       is_best_restaurant,
       is_cesco,
       datediff(now(), created_at) as sales_days,
       estimated_delivery_time,
       image_url,
       is_deliver
from restaurant
         left outer join (
    select restaurant_id,
           round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score,
           count(*)                                                 as review_num,
           count(review_owner_comment.contents)                     as owner_comment_num
    from (review
        left outer join orders using (order_id))
             left outer join review_owner_comment using (review_id)
    group by restaurant_id
) as t using (restaurant_id)
where restaurant.type = ?
  and restaurant.our_village_plus = 'Y'
  and restaurant.region = (select region from users where user_id = ?);


-- 2.2. (2.1.)에 속하는 가게들의 메뉴별 판매량
select restaurant_id, menu_name, sales
from menu
         left outer join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t using (menu_id)
where restaurant_id in (
    select restaurant_id
    from our_village_plus
             join restaurant using (restaurant_id)
    where our_village_status = 'Y'
      and restaurant.type = 'chicken'
)
order by restaurant_id, sales desc;


-- 2.3 .사용자 지역 == 식당 지역 && 슈퍼레드위크 && 식당 분류가 '치킨'인 식당 정보
select super_red_week,
       restaurant_name,
       restaurant_id,
       restaurant.region,
       score,
       review_num,
       t.owner_comment_num,
       concat(delivery_discount,'원') as delivery_discount,
       discount_rate,
       is_best_restaurant,
       is_cesco,
       datediff(now(), created_at) as sales_days,
       estimated_delivery_time
from restaurant
         left outer join (
   select restaurant_id, round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score, count(*) as review_num, count(review_owner_comment.contents) as owner_comment_num
from (review
         left outer join orders using (order_id)) left outer join review_owner_comment using (review_id)
group by restaurant_id
) as t using (restaurant_id)
where restaurant.type = ? and super_red_week = 'Y' and restaurant.region = (select region from users where user_id=?);

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
from super_red_week join restaurant using (restaurant_id)
    where red_week_status ='Y' and restaurant.type='치킨'
    )
order by restaurant_id, sales desc;

-- 3.1. 사용자 지역 == 식당 지역 && 식당 분류가 '치킨'인 식당 정보
select restaurant_name,
       restaurant_id,
       restaurant.region,
       score,
       review_num,
       t.owner_comment_num,
       concat(delivery_discount,'원') as delivery_discount,
       discount_rate,
       is_best_restaurant,
       is_cesco,
       datediff(now(), created_at) as sales_days,
       estimated_delivery_time,
       image_url,
       is_deliver
from restaurant
         left outer join (
    select restaurant_id,
           round(avg((taste_score + quantity_score + delivery_score) / 3),1) as score,
           count(*)                                                 as review_num,
           count(review_owner_comment.contents)                     as owner_comment_num
    from (review
        left outer join orders using (order_id))
             left outer join review_owner_comment using (review_id)
    group by restaurant_id
) as t using (restaurant_id)
where super_red_week = 'N'
  and our_village_plus = 'N'
  and restaurant.type = ?
  and restaurant.region = (select region from users where user_id = ?);



-- 3.2. (3.1.)에 속하는 식당들의 메뉴별 판매량
select restaurant_id, menu_name, sales
from menu
         left outer join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t using (menu_id)
where restaurant_id in (
    select restaurant_id
    from restaurant
    where type = 'chicken' && region = (select region from users where user_id = '10000001')
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
select  menu_name, menu_id, concat(price,'원') as price, image_url, sales
from menu
         natural join (
    select menu_id, sum(quantity) as sales
    from ordered_menu
    group by menu_id
) as t
where restaurant_id=?
order by restaurant_id, sales desc limit 2;

-- 5.2. 카테고리별 메뉴
select category, menu_name, menu_id, concat(price,'원') as price, image_url from menu
where restaurant_id=?;

-- 6. 후참잘 추가옵션
select menu_name, option_type, option_id, option_name, concat(extra_charge,'원') as extra_charge, share_url
from additional_option join menu using (menu_id)
where menu_id=?
order by option_type;

-- 7. 리뷰
select review_id, menu.restaurant_id, nickname, profile_image_url.image_url, group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info, group_concat(distinct review_image.image_url separator ';') as review_image_url,
       case
    when timestampdiff(hour,review.created_at, now()) <1 then concat(timestampdiff(minute,review.created_at, now()), '분전')
    when timestampdiff(day,review.created_at, now()) <1 then concat(timestampdiff(hour,review.created_at, now()), '시간전')
    when timestampdiff(day,review.created_at, now()) <2 then '어제'
    when timestampdiff(day,review.created_at, now()) <7 then concat(timestampdiff(day,review.created_at, now()), '일전')
else    date_format(review.created_at, '%Y.%m.%d %H:%i')
end as review_submit_time,
       round((taste_score + quantity_score + delivery_score) / 3,1) as score,
       taste_score, quantity_score,  delivery_score, review.contents, review_owner_comment.contents,
         case
    when timestampdiff(hour,review_owner_comment.created_at, now()) <1 then concat(timestampdiff(minute,review_owner_comment.created_at, now()), '분전')
    when timestampdiff(day,review_owner_comment.created_at, now()) <1 then concat(timestampdiff(hour,review_owner_comment.created_at, now()), '시간전')
    when timestampdiff(day,review_owner_comment.created_at, now()) <2 then '어제'
    when timestampdiff(day,review_owner_comment.created_at, now()) <7 then concat(timestampdiff(day,review_owner_comment.created_at, now()), '일전')
else    date_format(review_owner_comment.created_at, '%Y.%m.%d %H:%i')
end as review_submit_time
from ((((((review left outer join orders using (order_id)) left outer join review_owner_comment using (review_id)) left outer join users using (user_id))
         left outer join profile_image_url using (type)) left outer join ordered_menu using (order_id)) left outer join menu using (menu_id)) left outer join review_image using (review_id)
where menu.restaurant_id = ?
group by review_id;


-- 8.1. 터치주문 수
select count(*) as touch_num
from orders
where user_id='10000001' and order_type='touch';

-- 8.2. 전화주문 수
select count(*) as call_num
from orders
where user_id=? and order_type='call';

-- 8.3. 주문번호별 가게 정보
select order_id, order_type, date_format(orders.created_at, '%Y.%m.%d %H:%i') as order_date, delivery_status, restaurant_name, restaurant.image_url,delivery_status, group_concat(distinct concat(menu_name, '/', quantity) separator ',') as order_info
       from ((orders left outer join ordered_menu using (order_id) ) left outer join restaurant using (restaurant_id)) left outer join menu using (menu_id)
where user_id='10000001' and order_type='touch'
group by order_id;


-- 9. 주문 상세보기
-- 9.1. 주문정보

select restaurant_name,delivery_status,order_id,date_format(orders.created_at, '%Y.%m.%d %H:%i') as order_date,
       concat((select sum(price + IFNULL(extra_charge, 0))
        from (select ordered_menu.order_id,menu_name,quantity,ordered_menu.price,option_name,ordered_option.extra_charge
              from ((ordered_menu left outer join ordered_option using (menu_id)) left outer join menu using (menu_id))
                       left outer join additional_option using (option_id)
              where ordered_menu.order_id = ?) as temp),'원' )as sum_menu_price,
       concat(orders.delivery_price, '원') as delivery_price,concat(orders.delivery_discount,'원')as delivery_discount,
       concat(((select sum(price + IFNULL(extra_charge, 0))
        from (select ordered_menu.order_id,menu_name,quantity,ordered_menu.price,option_name,ordered_option.extra_charge
              from ((ordered_menu left outer join ordered_option using (menu_id)) left outer join menu using (menu_id))
                       left outer join additional_option using (option_id)
              where ordered_menu.order_id = ?) as temp) + orders.delivery_price - orders.delivery_discount),'원') as actual_price,
       orders.payment_type,users.phone,concat(users.region, ' ', users.address) as user_location ,request
from (orders join restaurant using (restaurant_id))
         join users using (user_id)
where orders.order_id = ?;


select ordered_menu.order_id, menu_name, quantity, ordered_menu.price, option_name, ordered_option.extra_charge
from ((ordered_menu left outer join ordered_option using (menu_id)) left outer join menu using (menu_id))  left outer join additional_option using (option_id)
where ordered_menu.order_id='200613-20-144783';



-- 10.1. 이용내역
-- 쿠폰개수, 포인트 구현 안함
select nickname, type, image_url, review_num
from (users join profile_image_url using (type)) join (select user_id, count(*) as review_num
from review left outer join orders using (order_id)
group by user_id) as t using (user_id)
where user_id='10000001';

-- 10.2. 개인정보
select email, phone, nickname
from users
where user_id='10000001';

-- 10.3. 등록한 카드
select *
from registered_card
where user_id='10000001';


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




insert into users (nickname, email, password, phone, region, address) values (?,?,?,?,?,?);


-- 주문표에 추가하기
insert into order_pad (restaurant_id, menu_id, quantity, option_id) values (?,?,?,?)

-- 식당 변경하기
CREATE TABLE order_pad
(
    `order_pad_id`   INT    NOT NULL    AUTO_INCREMENT,
    `restaurant_id`  INT    NOT NULL,
    `menu_id`        INT    NOT NULL,
    `option_id`      INT    NOT NULL    DEFAULT 0,
    `quantity`       INT    NOT NULL    DEFAULT 1,
    PRIMARY KEY (order_pad_id, menu_id, option_id)
);

-- 식당 찜하기(추가)
insert into favorite_restaurant
(user_id, restaurant_id) values (10000001, ?);

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
where user_id='10000001' and restaurant_id=?;

-- 카드 존재 여부
SELECT EXISTS(select * FROM registered_card WHERE card_number = ?) as exist;

-- 카드 삭제
insert into registered_card values ('10000001',?,?,?,?,?,?,'0');

-- 결제 비밀번호 조회
select payment_password
from users
where user_id=10000001;

-- 결제 비밀번호 변경
update users set payment_password = ? where user_id=10000001;

-- 휴대전화번호 변경
update users set phone = ? where user_id=10000001;

-- 닉네임 변경
update users set nickname = ? where user_id=10000001;

-- 주문표 메뉴 삭제
delete from order_pad
where menu_id = ? and option_id=?;

insert into order_pad (restaurant_id, menu_id, option_id, quantity) values ('20000001', '30000002', '50000002', '3');

SELECT EXISTS (select * FROM order_pad WHERE menu_id = ? and option_id=?) as exist;

truncate order_pad;

select restaurant_id
from order_pad limit 1;

SELECT NOT EXISTS (select * FROM order_pad) as exist;

select price from menu where menu_id=?;
select extra_charge from additional_option where option_id=?;

insert into orders (order_id, restaurant_id, user_id, payment_type, request, order_type, delivery_price, delivery_discount)
values (?,?,?,?,?,?,?,?);

insert into ordered_menu values (?,?,?,?);

