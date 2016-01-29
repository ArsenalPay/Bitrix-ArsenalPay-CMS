# Bitrix-ArsenalPay-CMS
Bitrix ArsenalPay CMS is software development kit for fast simple and seamless integration of your Bitrix web site with processing server of ArsenalPay.

*Arsenal Media LLC*

[Arsenal Pay processing center](https://arsenalpay.ru/)

Basic feature list:

 * Allows seamlessly integrate unified payment frame into your site.
 * New payment method ArsenalPay will appear to pay for your products and services.
 * Allows to pay using mobile commerce and bank aquiring. More methods are about to become available. Please check for updates.

### О МОДУЛЕ
Модуль платежной системы "ArsenalPay" под BITRIX позволяет легко встроить платежную страницу на Ваш сайт.
После установки модуля у Вас появится новый вариант оплаты товаров и услуг через платежную систему "ArsenalPay".
Платежная система "ArsenalPay" позволяет совершать оплату с различных источников списания средств:
мобильных номеров (МТС/Мегафон/Билайн/TELE2), пластиковых карт (VISA/MasterCard/Maestro).
Перечень доступных источников средств постоянно пополняется. Следите за обновлениями.

За более подробной информацией о платежной системе ArsenalPay зайдите на [arsenalpay.ru](https://arsenalpay.ru).

### УСТАНОВКА 
#### Через маркет (предпочтительный способ):
1. Зайдите на [1С БИТРИКС МАРКЕТПЛЕЙС](http://marketplace.1c-bitrix.ru/solutions/arsenalmedia.arsenalpay)
2. Нажмите на "Установить"
3. Укажите адрес сайта Вашего магазина
4. Следуйте инструкциям в администировании Bitrix Вашего интернет-магазина

#### Через администрирование Bitrix:
1. Зайдите в администрирование BITRIX;
2. Выберите закладку "Marketplace" в левом меню;
3. Выберите пункт "Каталог решений";
4. Введите в поиске "arsenalmedia";
5. Найдите в списке модуль ArsenalPay и нажмите "Установить".

#### Вручную:
1. Скопируйте папку arsenalmedia.arsenalpay в каталог "bitrix\modules"
2. Зайдите в администрирование BITRIX;
3. Выберите закладку "Marketplace" в левом меню;
4. Выберите пункт "Установленные решения";
3. Найдите в списке "Модуль платежной системы arsenalpay.ru" и нажмите "Установить".
7. После установки выберите в левом меню вкладку "Магазин";
8. Разверните пункт "Настройки";
9. Выберите пункт "Платежные системы";
10. Нажмите кнопку "Добавить платежную систему";
11. На вкладке "Типы плательщиков" выберите обработчик "Платежная система арсенал медиа" и заполните свойство обработчика "Телефон", по стандарту Тип - Свойство заказа, Значение - Телефон;

### НАСТРОЙКА
1. Зайдите в администрирование BITRIX;
2. Выберите закладку "Настройки" в левом меню;
3. Выберите пункт "Настройки продукта";
4. Разверните пункт "Настройки модулей";
5. Выберите пункт "Модуль платежной системы arsenalpay.ru";
6. Заполните необходимые настройки и нажмите сохранить;

### УДАЛЕНИЕ
1. Зайдите в администрирование BITRIX;
2. Выберите закладку "Настройки" в левом меню;
3. Выберите пункт "Настройки продукта";
4. Выберите раздел "Модули";
5. Найдите в списке "Модуль платежной системы arsenalpay.ru" и нажмите "Удалить";

### ИСПОЛЬЗОВАНИЕ
После успешной установки и настройки модуля на сайте появится возможность выбора платежной системы "ArsenalPay".
Для оплаты заказа с помощью платежной системы "ArsenalPay" нужно:

1. Выбрать из каталога товар, который нужно купить;
2. Перейти на страницу оформления заказа (покупки);
3. В разделе "Платежные системы" выбрать платежную систему "ArsenalPay";
4. Перейти на страницу подтверждения введенных данных и ввода источника списания средств (мобильный номер, пластиковая карта и т.д.);
5. После ввода данных об источнике платежа в зависимости от его типа, Вам либо придет СМС о подтверждении платежа, либо Вы будуете перенаправлены на страницу с результатом платежа;
6. Результат оплаты заказа поступит на адрес "Url колбэка" для фиксирования его в системе предприятия (обновление статуса заказа). Колбэк доступен по адресу "http://адрес_вашего_сайта/callback/index.php", исходный код колбэка в "/bitrix/modules/arsenalmedia.arsenalpay/install/components/arsenalmedia/callback/component.php" (после внесения изменений в колбэк нужно переустановить модуль);
7. При необходимости осуществления проверки номера получателя перед совершением платежа, Вы должны заполнить поле "Url проверки номера получателя", на который от "ArsenalPay" поступит запрос на проверку.
8. Модуль arsenalmedia.arsenalpay позволяет установить один из методов оплаты - с баланса мобильного либо с карт. Для подключения второго типа оплаты необходимо установить второй модуль arsenalpay.second. [Инструкция по подключению второго модуля.](https://github.com/ArsenalPay/Bitrix-ArsenalPay-CMS/blob/master/arsenalpay.second/Readme.md) 

------------------
### ОПИСАНИЕ РЕШЕНИЯ
ArsenalPay – удобный и надежный платежный сервис для бизнеса любого размера. 

Используя платежный модуль от ArsenalPay, вы сможете принимать онлайн-платежи от клиентов по всему миру с помощью: 
пластиковых карт международных платёжных систем Visa и MasterCard, эмитированных в любом банке
баланса мобильного телефона операторов МТС, Мегафон, Билайн, Ростелеком и ТЕЛЕ2
различных электронных кошельков.

### Преимущества сервиса: 
 - [Самые низкие тарифы](https://arsenalpay.ru/tariffs.html)
 - Бесплатное подключение и обслуживание
 - Легкая интеграция
 - [Агентская схема: ежемесячные выплаты разработчикам](https://arsenalpay.ru/partnership.html)
 - Вывод средств на расчетный счет без комиссии
 - Сервис смс оповещений
 - Персональный личный кабинет
 - Круглосуточная сервисная поддержка клиентов 

А ещё мы можем взять на техническую поддержку ваш сайт и создать для вас мобильные приложения для Android и iOS. 

ArsenalPay – увеличить прибыль просто! 
Мы работаем 7 дней в неделю и 24 часа в сутки. А вместе с нами множество российских и зарубежных компаний. 

### Как подключиться: 
1. Вы скачали модуль и установили его у себя на сайте;
2. Отправьте нам письмом ссылку на Ваш сайт на pay@arsenalpay.ru либо оставьте заявку на [сайте](https://arsenalpay.ru/#register) через кнопку "Подключиться";
3. Мы Вам вышлем коммерческие условия и технические настройки;
4. После Вашего согласия мы отправим Вам проект договора на рассмотрение.
5. Подписываем договор и приступаем к работе.

Всегда с радостью ждем ваших писем с предложениями. 

pay@arsenalpay.ru 

[arsenalpay.ru](https://arsenalpay.ru)
