# Opdracht UDP_TCP

Dit is een voorbeeldproject waarin we werken met UDP- en TCP-communicatie in Python (of jouw taal). Dit project bevat basisimplementaties van netwerkclients en servers.

## Beschrijving

Dit project demonstreert de basisprincipes van netwerkprogrammering met UDP en TCP. Het bevat eenvoudige scripts voor het opzetten van servers en clients die met elkaar communiceren via deze protocollen.

## Installatie

Zorg dat je Python 3.x (of jouw gebruikte taal) hebt geïnstalleerd.

Clone de repository:

```bash
git clone https://github.com/feefranssen/Opdracht_UDP_TCP.git
cd Opdracht_UDP_TCP
```

(Voeg eventueel dependency-installatie toe als nodig:)

```bash
pip install -r requirements.txt
```

## Gebruik

### UDP

Start eerst de UDP-server:

```bash
python udp_server.py
```

Start dan de UDP-client in een andere terminal:

```bash
python udp_client.py
```

### TCP

Start eerst de TCP-server:

```bash
python tcp_server.py
```

Start dan de TCP-client in een andere terminal:

```bash
python tcp_client.py
```

## Structuur

```
Opdracht_UDP_TCP/
├── udp_server.py
├── udp_client.py
├── tcp_server.py
├── tcp_client.py
├── README.md
└── requirements.txt
```

## Licentie

Dit project valt onder de MIT-licentie. Zie `LICENSE` voor details.



