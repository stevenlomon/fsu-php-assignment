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