# openbudget-vote-bot

Openbudget loyihasi uchun ovoz yig'uvchi bot. Ushbu bot orqali to'g'ridan to'g'i saytga ovoz to'plash uchun raqam yuborish va uni sms orqali tasdiqlash mumkin. Shuningdek bot faoliyati davomida ovozlar uchun to'lovlarni nazorat qilishni ham o'z ichiga oladi.

Bot ikki qismga bo'lingan, admin paneli va user panel.

## O'rnatish

1. `hook.php` va `crone.php` fayllaridan bot tokenini o'zingizdagi botga moslang.
2. `crone.php` faylini cronjobga qo'shing:

```
@reboot /usr/bin/php /home/user/bot/crone.php > /dev/null 2>&1
```

3. `opb.php` faylini tasix internet doirasida bo'lgan istalgan serverga joylang va `functions.php` faylidan `api()` funksiyasi uchun fayl o'rnashgan lokatsiyani kiriting.

4. `hook.php` faylini `setWebhook` orqali telegram apiga ulang 

5. `data/owners.dat` faylida kerakli adminitratorning telegram idenfikatorini kiriting 