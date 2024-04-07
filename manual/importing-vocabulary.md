# Importing Vocabulary into LinguaCafe

If you have a list of words that you already know before you started using LinguaCafe, you can import them from a CSV file.

>[!CAUTION]
>
> Changes after importing cannot be reverted, thus make sure you're importing only the words you want LinguaCafe to track.

To import words, go to the **Vocabulary** page, select the **Data** dropdown menu, and inside that click on the **Import** button. On the import dialog you can select your CSV file and a few options: 
- **Skip first row**. If enabled, LinguaCafe skips the first row which could be simply be the column names.
- **Only update**. If enabled, no new words will be added to the system. This allows you to only update fields for words that you have already encountered in LinguaCafe.

The CSV file can have these columns, in this order:

| Column Name | Required | Accepted Values | Comment |
| :--- | :--- | :--- | :--- |
| Word | Yes | Any word without any spaces. |  |
| Translation | No |  | Can be left empty. |
| Lemma | No |  | Can be left empty. |
| Reading | No |  | Can be left empty. |
| Lemma reading | No |  | Can be left empty. |
| Level | No | `new`, `ignored`, `learned`, `1`, `2`, `3`, `4`, `5`, `6`, `7` | Cannot be left empty. |

At least the first column must be present in the CSV file. Any further columns can be added to it in the order showed above. If a column is not provided, those fields will not be changed in the database. However if a column is provided, and it's left empty in a row, it will be overwritten in the database with an empty value.

After the import is complete, you will see a message about the number of created, updated and rejected words.