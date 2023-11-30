Sample Scheduler implementation
===============================

Popis problému:
Cílem je mít systém, co nejméně vázaný na konkrétní implementace a knihovny třetích stran. Proto většinu věcí řešíme přes proxy/adaptér.
Nyní chceme do systému integrovat Scheduler(plánovač tasku). Vyhlídli jsme dva https://laravel.com/docs/9.x/scheduling(ten je součástí framerowku) a
alternativní https://github.com/crunzphp/crunz.

Aktuálně použijeme Laravel, ale nechceme se na něho vázat a mít případně otevřenou cestu k přepnutí na crunz. V rámci implementace
musíme vyřešit i problém, kdy Laravel Mutex implementace je nespolehlivá a chceme implementovat vlastní mutext.

Cíle:
Cílem je navrhnout interface jednotlivých tříd pro Scheduler a Mutex, tak aby implementace Laravel tříd byla kdykoli vyměnitelná a nevázala se na Laravel.

V rámci návrhu je nutné jít do detailu, včetně vstupních parametrů a jejich typů. Aktuální implementace by měla být schopna plánovat a spouštět crony/joby v daný čas.

Součástí návrhu je i návrh konkrétní implementace pro Laravel Scheduler a Mutext.


## Řešení:

Základní premisy:
1. Máme nějaké rutiny, které chceme v určitých intervalech spouštět. – Jak budou tyto rutiny realizovány? Jak budou plánovány? Jak bude řešený výstup?
2. Rutiny se podle nějakého kalendáře plánují.
3. Chceme v maximální míře použít existující řešení, ne si psát něco vlastního.
4. Pokud jsem něco nepřehlédl, tak crunz umí pouze spouštět podprocess. Což vlastně omezuje i prvotně zvažované řešení Laravel Sheduling.

Co tedy vlastně chceme zobecňovat?
- Rutiny budou definovány jako subprocesy. Je možné použít libovolný třeba script, ale očekávám, že nejčastěji budou používány commandy artisanu - což je ale také script/suprocess. To nám umožní použít pro vlastní scheduling libovolný ze zvažovaných systémů.
- Prohlížení existujících tasků můžeme použít původní nástroj (Laravel Sheduling, Crunz), ale chceme ho zapojit do systému, tedy ovládat programově. Zde tedy vytvoříme nějaký adaptér.
	- adaptér může wrapovat příkazovou řádku
	- nebo může obcházet systém a vytvářet tasky přímo

Potřebujeme:
- výpis existujících tasků
- vytvoření nového tasku
- editaci existujícího tasku
- smazání tasku
- spouštění celého schedulingu

Poznámka: Připomínám, že se nejedná o vlastní rutiny. Ty jsou definovány jinde. Ale o task, který tuto rutinu bude pouštět a kdy.
