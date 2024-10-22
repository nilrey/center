/*
 * Обрабочтчик события удаления записи в таблице
 *
 *ITEM_ID: Идентификатор удаляемой записи из таблицы
 *URLString: URL-адрес по которому выполняется запрос удаления данных
*/
 function onDeleteTableRecord(ITEM_ID, URLString)
{
    deleteTableItemDialog('dialog_rec_delete',
        'item_' + ITEM_ID,
        'dTable',
        URLString,
        'POST',
        "Ошибка удаления записи. Обратитесь к администратору сайта."
        );
}