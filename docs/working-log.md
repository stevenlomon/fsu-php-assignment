## Aug 30
Vi har vår `docker-compose.yml` och `Dockerfile`. Så nu kör vi 
```
docker compose up -d --build
```
för att skapa containern. De två filerna är kopior av det jag lekt runt med nu i helgen för att bygga en liten foundation i php. Den containern jag använda har jag kört `compose stop` och `compose down` på så att vi kan återanvända container arkitekturen helt på samma ports.  
Detta körs även
```
sudo chown -R $USER:$USER ./html
```
på min Linux maskin för att få ownership över `./html`.  
Output från `docker compose ps` nu:  
```
stevenlomon@steven-pop-os:~/fullstack/fsu-php-assignment$ docker compose ps
NAME                              IMAGE                  COMMAND                  SERVICE      CREATED          STATUS          PORTS
fsu-php-assignment-db-1           mariadb:10.6           "docker-entrypoint.s…"   db           45 seconds ago   Up 44 seconds   3306/tcp
fsu-php-assignment-phpmyadmin-1   phpmyadmin             "/docker-entrypoint.…"   phpmyadmin   45 seconds ago   Up 44 seconds   0.0.0.0:8081->80/tcp, [::]:8081->80/tcp
fsu-php-assignment-site-1         php-assignment:local   "docker-php-entrypoi…"   site         45 seconds ago   Up 44 seconds   0.0.0.0:8082->80/tcp, [::]:8082->80/tcp
stevenlomon@steven-pop-os:~/fullstack/fsu-php-assignment$
```
Så nu på root @8082 borde vi se...  
![PHP 8.4.25 running cleanly](./screenshots/Screenshot_2026-08-30_13-15-56.png)  
Wonderful 🥳  

Låt oss nu översätta [vår sitemap från vår pre-work](pre-work.md#sitemap)  
```
/  
/register  
/profile  
/groups  
/groups/:id  
/groups/:id/discussions  
/groups/:id/discussions/:id  
``` 
till en file based router. Precis som Next.js!! Apparently så moderniserade Next.js det som PHP etablerade, riktigt cool dot to connect!  

Och jag märker här haha...  
Min första instinkt var att sätta upp det som vi skulle i Next.js!  
![Första instinkt till file based router, likt Next.js](./screenshots/Screenshot_2026-08-30_14-03-35.png)  
Men efter att ha bollat lite med AI finns det två stora downsides med detta:  
1. I php kan vi inte använda path aliases som `(@/lib/db)` lika lätt som vi kan i Next.js  
2. I Gemini's ord:  
> Flat files (groups.php, group.php, topic.php, register.php, login.php) align with how Apache natively routes requests and keep your require statements simple.  

Så vi the Flat Script Files (`/groups.php`, `/topics.php`) approach istället för the Nested Directory (`/groups/index.php`, `/topics/index.php`) approach! Vilket betyder att vår sitemap helt enkelt översätts till
```
html/
├── db.php
├── index.php
├── register.php
├── login.php
├── profile.php
├── groups.php
└── group.php
```
detta!  
Det som jag från Next.js tänker `/html/groups/[id]/index.php` blir helt enkelt `/group.php?id=id`!  

Oxå; i och med att detta inte är Next.js och vi inte kan tänka komponent vis och hålla state lätt..  
> Tänker något sånt här med login i Navbar samt ett knapp för att bli medlem haha:  
> ![Navbar med login och "Bli medlem" knapp](./screenshots/Screenshot_2026-08-30_12-57-31.png)  

Jag tar tillbaka detta, jag vill bara att arkitekturen ska vara så simpel som bara möjlig med så lite head ache som bara möjligt haha.  

![Vår nya file based router](./screenshots/Screenshot_2026-08-30_14-29-03.png)  
Så där har vi nu istället då!  

Vår `db.php` som innehåller vår databaskoppling ligger i `/html/` istället för root för att... pga hur vår Docker container är uppsatt om jag förstår rätt. Men den representerar inte en sida alls.  

Nu när jag håller på att be Gemini översätta min ERD till SQL pekar den ut en väldigt valid point:  
> reply_to Reference: In the diagram, reply_to links to User(UniqueID). If you meant for replies to reference the parent post instead (for threaded comment trees), change that foreign key to FOREIGN KEY (reply_to) REFERENCES posts(id) ON DELETE SET NULL.  

Det är smidigare att `reply_to` är en self-referential FK till en `Post` eller `NULL` för vi kan hitta `User` via `Post`!  
Så vår nya ERD..  
![ERD v1.2](./erd/Screenshot_2026-08-30_14-45-07.png)  
There we go.  

Och detta finns nu översatt till SQL i denna [schema.sql fil](./db/schema.sql). Dags att köra det i phpMyAdmin.  
![SQL ser inte ut att returnera errors](./screenshots/Screenshot_2026-08-30_14-54-06.png)  
Ser lovande ut! Och...  
![Våra 5 tabeller skapade i phpMyAdmin](./screenshots/Screenshot_2026-08-30_14-55-19.png)  
Snyggt 🥳  

## Sep 1
Kodade inte på detta alls igår, oh well! Ska få till lite nu iaf.  
Jag är på the Home page. Från den ska vi länka till:  
* groups.php  
* register.php
* login.php
* profile.php

Let's tackle register!  
Det känns som att jag har tappat lite vanilla HTML från att ha suttit så mycket med React och Next.js haha. `<form action="">` Vilket `action` vill vi ha här? Det här.. ska ju kommunicera med vår databas. Jag tror jag kommer skippa all validering och nu bara fråga AI hur jag fångar form data och får in det i min databas.  
"Can you give a few hints to kickstart my intuition?" Tänker skriva till Gemini så här nu i början så mycket jag kan så att jag lär mig så php så djupt som möjligt. Men från first principles! Och det är aldrig time wasted för jag kommer kunna connect dots och compare and contrast när jag går tillbaka till TS och Next.js!  

> To make the mental shift from React/Next.js, remember that HTML forms work natively without useState, onChange, or fetch(). The browser itself packages the input fields and sends them directly to PHP.

> **The 3 HTML Fixes (Unlearning Reactisms)**
> 
> Your form needs three essential adjustments before PHP can read any data:
> 
> - **The `name` Attribute:** In vanilla HTML/PHP, `id` is only for CSS/JS. PHP populates `$_POST` strictly based on the **`name`** attribute: `<input name="username">` $\to$ `$_POST['username']`.
>     
> - **The Method & Submit Button:** Specify `method="POST"` on the form and add a `<button type="submit">`.
>     
> - **HTML Structure:** In HTML, `for` is used instead of JSX's `htmlFor`, and all visible elements must sit inside `<body>`, not `<head>`.

Off to a flying start haha!  

Jag märker nu oxå, och ställde frågan. Jag förstår det som att register.php skickar en POST request.. till sig själv?? It kinda does!

> **Next.js vs. Plain PHP Comparison**
> 
> - **Next.js:** You typically create a UI component in `page.tsx` and post data to a separate `route.ts` API endpoint or invoke a Server Action.
>     
> - **Plain PHP:** `register.php` handles both serving the UI and processing the mutation in one cohesive script.  

Interesting!

Lite ändringar här i och med `if ($_SERVER['REQUEST_METHOD'] === 'POST')` satsen och databaskopplingen:  
* Skippar email i register form. Det sätts på profile.php istället. Användare kommer logga in enbart med username och password
* fname och lname är nu båda nullable i databasen så att de sätts till `NULL` i vår POST request, inte ''. `NULL` är mer true  

Let's try it!  
`Fatal error: Uncaught mysqli_sql_exception: Column 'email' cannot be null in /var/www/html/register.php:17 Stack trace: #0 /var/www/html/register.php(17): mysqli->query('INSERT INTO use...') #1 {main} thrown in /var/www/html/register.php on line 17`
Whoops.  
* email är oxå nullable nu haha!

![Success!](./screenshots/Screenshot_2026-09-01_19-53-53.png)  
Succesfully redirected till login.php!  

![Data i databasen!](./screenshots/Screenshot_2026-09-01_19-58-54.png)  
Och vi har data i databasen!! Men inte username? Det här blir att utreda imorn, I'm gonna call it a day here! Viktiga reps idag  

## Sep 4
Let's investigate varför username inte sparas i databasen.  
```
$username = trim($POST['username'] ?? ''); //TODO: Varför blir denna tom i databasen?
$password = trim($POST['password'] ?? '');
```  
Tänk vad nyttigt det är att ta ett steg bort från koden och komma tillbaka med fräscha ögon. Jag kör ju med `$POST`, inte `$_POST` haha! Det här är most likely felet. Let's fix it and try again..  
![All user data kommer nu in i databasen](./screenshots/Screenshot_2026-09-04_05-50-59.png)  
Let's go. Moving on!  