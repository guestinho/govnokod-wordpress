#### Переписываем http://govnokod.ru на WordPress

# Инструкция по установке

1. **Ставим WordPress** https://wordpress.org/download/ (тестируемая версия 4.9.4)

2. **Устанавливаем тему** [theme.zip](https://github.com/guestinho/govnokod-wordpress/releases "theme.zip")

3. После активации оно попросит установить 2 необходимых плагина (Govnokod и Ultimate Member) - **устанавливаем их**.

4. Автоматически создались страницы профиля, формы, директории пользователей. **Мы их удалаям**. Из корзин тоже.
   
   Pages -> All Pages

   Ultimate Member -> Forms

   Ultimate Member -> Member Directories


5. Вместо них **экспортируем заранее подготовленные**. https://github.com/guestinho/govnokod-wordpress/tree/master/plugins/govnokod/data (они же лежат а архиве с плагином)

   Для всех 3-х xml-ек делаем

   Tools -> Import -> WordPress (при необходимости установить - кликнуть install) -> Run Importer -> импортируем на юзера admin


6. Далее **импортируем настройки**.

   Ultimate Member -> Settings -> Advanced -> Import from file -> вставляем туда содержимое um.json -> Import


7. **Настраиваем страницы**.

   Ultimate Member -> Settings -> Setup -> указываем какие страницы для чего.
  
   Для этих страниц фиксим айдишники форм. Ходим по всем этим страницам Pages -> All Pages и исправляем айдишники форм на правильные. 

   Посмотреть правильные можно в Ultimate Member -> Forms и Ultimate Member -> Member Directories


8. Можно **создать меню**.

   Appearance -> Menus -> натаскиваем себе менюху -> внизу ставим галочку "Top Menu" -> Save Menu


#### Готово.
