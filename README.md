# Opdracht UDP_TCP

Dit is ons project waarin we werken met verschillende programmeer talen. Zoals php, HTML en java.
## Beschrijving

Dit project bevat een local host webserver die het weer weergeeft ;) 

## Installatie
```bash
sudo apt install php8.3-fpm
sudo apt install caddy
sudo apt install postgresql postgresql-contrib php-pgsql
sudo apt install net-tools
sudo apt update
sudo apt install apache2 php php-pgsql 
```

Clone de repository:

```bash
git clone https://github.com/Easy1245/Webtechnologie-Project-.git
```

## Gebruik

Om de caddy file werkend te krijgen :
```bash
sudo systemctl start php8.3-fpm

sudo lsof -i :80

sudo systemctl stop apache2 

sudo systemctl start caddy

sudo systemctl restart caddy

sudo systemctl status caddy

sudo systemctl stop caddy

sudo caddy run --config /mnt/c/group_project/caddyfile --adapter caddyfile

```
## Databas

Om de database te bekijken
```bash
psql -h localhost -p 5432 -U admin -d webtechhelp

\dt

SELECT * FROM weather_data; 

```

## Licentie

Dit project valt onder de MIT-licentie. Zie `LICENSE` voor details.



