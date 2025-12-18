<<<<<<< HEAD
# COINS-GP
Une plateforme collaborative qui transforme l’espace de bagage aérien  inutilisé des voyageurs en solution de livraison économique pour les particuliers.
=======
# Coins GP Project
# COINS GP 🚚📦

COINS GP est une plateforme web de mise en relation entre **GP Bagage** et **Clients**.
Elle permet aux clients de trouver rapidement un GP disponible selon une destination
et de réserver le transport de leurs bagages de manière sécurisée.

---

## 🎯 Objectifs du projet
- Centraliser les GP Bagage sur une plateforme unique
- Faciliter la recherche de GP par destination
- Digitaliser les réservations de bagages
- Améliorer la transparence et la fiabilité du service

---

## 👥 Acteurs de la plateforme
- **GP Bagage** : proposent des trajets et transportent les bagages
- **Clients** : recherchent un GP et effectuent des réservations
- **Administrateur** : valide les GP et supervise la plateforme

---

## ⚙️ Fonctionnalités principales
- Inscription et authentification (JWT)
- Recherche automatique des GP par destination
- Réservation et suivi des bagages
- Tableaux de bord GP / Client / Admin
- Validation des GP par l’administrateur
- Statistiques globales

---

# COINS-GP

COINS GP est une plateforme web de mise en relation entre **GP Bagage** et **Clients**.
Elle permet aux clients de trouver rapidement un GP disponible selon une destination
et de réserver le transport de leurs bagages de manière sécurisée.

---

## 🎯 Objectifs du projet
- Centraliser les GP Bagage sur une plateforme unique
- Faciliter la recherche de GP par destination
- Digitaliser les réservations de bagages
- Améliorer la transparence et la fiabilité du service

---

## 👥 Acteurs de la plateforme
- **GP Bagage** : proposent des trajets et transportent les bagages
- **Clients** : recherchent un GP et effectuent des réservations
- **Administrateur** : valide les GP et supervise la plateforme

---

## ⚙️ Fonctionnalités principales
- Inscription et authentification (JWT)
- Recherche automatique des GP par destination
- Réservation et suivi des bagages
- Tableaux de bord GP / Client / Admin
- Validation des GP par l’administrateur
- Statistiques globales

---

## 🛠️ Stack technique
- **Frontend** : React + Vite + Bootstrap
- **Backend** : Laravel 10 (API REST)
- **Base de données** : MySQL ou PostgreSQL
- **Sécurité** : JWT, rôles (admin, gp, client)
- **Déploiement** : Docker + Nginx

---

## 🚀 Lancement rapide (Docker)
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
docker-compose up -d --build
```
