<?php
// Heading
$_['heading_title']              = 'Frame Free: Слайдшоу, Карусель, Баннер';

// Buttons
$_['button_apply']               = 'Применить и остаться';
$_['button_save']                = 'Сохранить и выйти';
$_['button_cancel']              = 'Выйти без сохранения';

// Text
$_['text_extension']             = 'Расширения';
$_['text_success']               = 'Настройки модуля успешно изменены!';
$_['text_edit']                  = 'Настройки модуля';
$_['text_settings']              = 'Основные настройки';
$_['text_enabled']               = 'Вкл.';
$_['text_disabled']              = 'Откл.';
$_['text_slideshow']             = 'Слайдшоу';
$_['text_single_banner']         = 'Баннер';
$_['text_carousel']              = 'Карусель';
$_['text_no_animation']          = 'Без анимации';
$_['text_pagination']            = 'Пагинация';
$_['text_arrows']                = 'Стрелки';
$_['text_slide']                 = 'Слайд';
$_['text_slides']                = 'Слайды';
$_['text_add_slide']             = 'Добавить слайд';
$_['text_ms']                    = 'мс';

// Entry
$_['entry_status']               = 'Статус';
$_['entry_name']                 = 'Имя модуля в системе';
$_['entry_title']                = 'Заголовок модуля';
$_['entry_width']                = 'Ширина изображения';
$_['entry_height']               = 'Высота изображения';
$_['entry_lazyload']             = 'Отложенная загрузка изображений';
$_['entry_type']                 = 'Тип модуля';
$_['entry_items']                = 'Количество видимых слайдов';
$_['entry_items_responsive']     = 'Контрольные точки адаптивности';
$_['entry_vievport']             = 'Вьюпорт';
$_['entry_parent']               = 'Родитель';
$_['entry_selector']             = 'Селектор';
$_['entry_responsive_base']      = 'База адаптивности';
$_['entry_breakpoint']           = 'Ширина (px)';
$_['entry_breakpoint_items']     = 'Кол-во слайдов';
$_['entry_breakpoint_add']       = 'Добавить точку';
$_['entry_animation']            = 'Анимация слайдов';
$_['entry_controls']             = 'Элементы управления';
$_['entry_autoplay']             = 'Авто прокрутка';
$_['entry_loop']                 = 'Зациклить прокрутку';
$_['entry_autoplay_speed']       = 'Скорость';
$_['entry_hd_images']            = 'Изображения высокой чёткости';
$_['entry_image']                = 'Изображение';
$_['entry_description']          = 'HTML-контент';
$_['entry_type_slide']           = 'Тип слайда';
$_['entry_type_img']             = 'Изображение';
$_['entry_type_html']            = 'HTML-контент';
$_['entry_img_size']             = 'Размер изображения';
$_['entry_alt']                  = 'Альт. текст';
$_['entry_link']                 = 'Ссылка';
$_['entry_target']               = 'Открывать в новом окне';
$_['entry_sort_order']           = 'Сортировка';

$_['placeholder_description']    = 'Ваш код...';
$_['placeholder_selector']       = 'Укажите селектор';
$_['editor_hilight']             = 'Подсветка кода';
$_['editor_autoheight']          = 'Безграничный ввод';

// Help
$_['help_title']                 = 'Заголовок, который будет выведен на странице перед каруселью. Оставьте это поле пустым, если заголовок не нужен';
$_['help_module_type']           = '&lt;p&gt;&lt;b&gt;Слайдшоу&lt;/b&gt; - карусель контента с одним блоком в области видимости&lt;/p&gt;&lt;p&gt;&lt;b&gt;Карусель&lt;/b&gt; - карусель контента с неограниченным количеством блоков в области видимости&lt;/p&gt;&lt;p&gt;&lt;b&gt;Баннер&lt;/b&gt; - набор изображений или html-блоков без использования JavaScript, все слайды будут выведены один за другим по порядку&lt;/p&gt;';

$_['help_items_responsive']      = 'Контрольные значения ширины, относительно которых будет пересчитано количество элементов в области видимости карусели.';
$_['help_responsive_base']       = '&lt;p&gt;Элемент, относительно ширины которого будут рассчитаны точки адаптивности.&lt;/p&gt; &lt;p&gt;&lt;b&gt;Вьюпорт&lt;/b&gt; - область видимости окна браузера или фрейма в котором отображается страница.&lt;/p&gt; &lt;p&gt;&lt;b&gt;Родитель&lt;/b&gt; - DOM-элемент который является родительским по отношению к карусели&lt;/p&gt;&lt;p&gt;&lt;b&gt;Селектор&lt;/b&gt; - селектор любого DOM-элемента на ваше усмотрение, например &lt;code&gt;#myCarousel&lt;/code&gt; или &lt;code&gt;.mySlideshow&lt;/code&gt;. Важно! Элемент должен находиться выше по дереву документа относительно карусели, иначе в качестве базы будет использован вьюпорт. Если элемент на странице не будет найден, так же будет использован вьюпорт.&lt;/p&gt;';

$_['help_animation']             = 'При включении данной опции, в документ будет подключена дополнительная css-библиотека анимаций animation.css';
$_['help_hd_images']             = 'Включение этой опции увеличит объём кэша. Но благодаря этому изображения будут выглядеть чётко на устройствах с высокой плотностью пикселей (смартфоны, планшеты, ретина дисплеи).';
$_['help_alt']                   = 'Альтернативный текст изображения';

$_['help_slide_html']            = '&lt;p&gt;Код, который будет отображён в слайдах типа &laquo;HTML-контент&raquo;.&lt;/p&gt;&lt;p&gt;Чтобы вставить в код адрес ссылки или изображения из настроек слайда используйте следующие синонимы:&lt;/p&gt;&lt;p&gt;&lt;i&gt;&lt;b&gt;{image}&lt;/b&gt; - ссылка на изображение&lt;br&gt;&lt;b&gt;{image2x}&lt;/b&gt; - увеличенное в 2 раза&lt;br&gt;&lt;b&gt;{image3x}&lt;/b&gt; - увеличенное в 3 раза&lt;br&gt;&lt;b&gt;{image4x}&lt;/b&gt; - увеличенное в 4 раза&lt;br&gt;&lt;b&gt;{width}&lt;/b&gt; - ширина изображения&lt;br&gt;&lt;b&gt;{height}&lt;/b&gt; - высота изображения&lt;br&gt;&lt;b&gt;{alt}&lt;/b&gt; - альтернативный текст&lt;br&gt;&lt;b&gt;{link}&lt;/b&gt; - адрес ссылки&lt;br&gt;&lt;b&gt;{target}&lt;/b&gt; - свойство target&lt;/i&gt;&lt;/p&gt;&lt;hr&gt;&lt;p&gt;Например &lt;code&gt;&lt;img src=&quot;{img}&quot; alt=&quot;{alt}&quot;&gt;&lt;/code&gt; или &lt;code&gt;&lt;a href=&quot;{link}&quot;&gt;...&lt;/a&gt;&lt;/code&gt;&lt;/p&gt;';

// Error
$_['error_permission']           = 'Внимание: У вас не достаточно прав для редактирования этого модуля!';
$_['error_name']                 = 'Имя модуля должно быть от 3 до 64 символов!';
