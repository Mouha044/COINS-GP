# Architecture
# Architecture Technique – COINS GP

---

## 1. Vue d’ensemble
COINS GP repose sur une architecture **client–serveur** basée sur une API REST.

Frontend (React)
|
| HTTP / JSON
v
Backend (Laravel API)
|
v
Base de données (MySQL / PostgreSQL)

yaml
Copier le code

---

## 2. Frontend
- Développé avec React
- Interface responsive (mobile-first)
- Appels API via Axios
- Gestion de l’authentification côté client

---

## 3. Backend
- Laravel 10
- API REST sécurisée par JWT
- Gestion des utilisateurs, trajets et réservations
- Séparation claire de la logique métier

---

## 4. Base de données
Tables principales :
- `users`
- `trips`
- `packages`
- `transactions`
- `ratings`

---

## 5. Sécurité
- JWT pour l’authentification
- Middleware de rôles
- Protection des routes sensibles
- HTTPS en production

---

## 6. Déploiement
- Conteneurisation avec Docker
- Nginx comme serveur web
- Séparation frontend / backend

---

## 7. Évolutivité
- Architecture modulaire
- Ajout facile de nouvelles fonctionnalités
- Intégration future d’API externes