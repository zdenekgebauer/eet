#PHP klient pro EET

Základní klient umožňující odeslat účtenku na EET server a získat FIK.

Implementace vychází z dokumentace http://www.etrzby.cz/assets/cs/prilohy/EET_popis_rozhrani_v3.1.1.pdf.
Přístupové údaje a certifikáty pro testovací prostředí jsou popsány 
v http://www.etrzby.cz/assets/cs/prilohy/EET_pristupove_provozni_informace_playground_3.1.pdf.

##Instalace

### Composer
`composer require zdenekgebauer/eet`

### Ruční instalace
Soubory ze složky src je třeba začlenit do aplikace pomocí autoloadu 
nebo pomocí `require_once 'src/autoload.php'` 

##Použítí 
Příklady použití jsou ve složce example. Příklad připojení na produkční EET používá testovací certifikát, 
pro ostré nasazení je třeba nastavit certifikát vystavený obchodníkovi.  
  
##Testy
V adresáři tests/integration jsou testy používajíci testovací i produkční server. U těchto serverů není možné 
simulovat selhání spojení, proto se používá volání skriptů na lokálním serveru. Před jejich 
spuštěním je třeba v souborech tests/_data/*.wsdl patřičně nastavit `soap:address location`.         

##Changelog
- 0.0.1
    - první verze
- 0.0.2 
    - doplnění metody `Connector:sign()` pro podepsání účtenky bez odeslání
    - doplnění metody `Receipt::getPkpString()` pro získání PHP kódu v base64
    - drobné opravy překlepů, odstranění zbytečností.  