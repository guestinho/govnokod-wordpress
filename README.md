Инструкция по установке

Ставим WordPress https://wordpress.org/download/ (тестируемая версия 4.9.4)

Устанавливаем тему govnokod.zip
После активации оно попросит установить 2 необходимых плагина (Govnokod и Ultimate Member) - устанавливаем их.

Автоматически создались страницы профиля, формы, директории пользователей. Мы их удалаям. Их корзин тоже.
Pages -> All Pages
Ultimate Member -> Forms
Ultimate Member -> Member Directories

Вместо них экспортируем заранее подготовленные. http://todo
Для всех 3-х xml-ек делаем
Tools -> Import -> WordPress (при необходимости установить - кликнуть install) -> Run Importer -> импортируем на юзера admin

Далее импортируем настройки.
Ultimate Member -> Settings -> Advanced -> Import from file -> вставляем туда содержимое um.json -> Import

Настраиваем страницы.
Ultimate Member -> Settings -> Setup -> указываем какие страницы для чего.
Для этих страниц фиксим айдишники форм. Ходим по всем этим страницам Pages -> All Pages и исправляем айдишники форм на правильные. Посмотреть правильные можно в Ultimate Member -> Forms и Ultimate Member -> Member Directories

Можно создать меню.
Appearance -> Menus -> натаскиваем себе менюху -> внизу ставим галочку "Top Menu" -> Save Menu

Готово.