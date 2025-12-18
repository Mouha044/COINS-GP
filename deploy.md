# Deployment Guide
# Guide de Déploiement – COINS GP

---

## 1. Objectif
Ce document décrit les étapes nécessaires pour déployer la plateforme COINS GP
de manière fiable, sécurisée et reproductible.

---

## 2. Prérequis
- Docker
- Docker Compose
- Git

---

## 3. Déploiement en local (Docker)

### 3.1 Clonage du projet
```bash
git clone https://github.com/coins-gp/coins-gp.git
cd coins-gp
3.2 Configuration des variables d’environnement
bash
Copier le code
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
Configurer :

Base de données

JWT_SECRET

Clés de paiement (si utilisées)

3.3 Lancement des services
bash
Copier le code
docker-compose up -d --build
3.4 Initialisation du backend Laravel
bash
Copier le code
docker exec -it coinsgp_backend bash
php artisan key:generate
php artisan migrate --seed
4. Déploiement en production
Désactiver APP_DEBUG

Activer HTTPS

Sécuriser l’accès admin

Mettre en place des sauvegardes

5. Mise à jour de l’application
bash
Copier le code
git pull
docker-compose down
docker-compose up -d --build
6. Maintenance
Surveillance des logs

Sauvegarde régulière de la base de données

Mise à jour des dépendances

7. Conclusion
Grâce à Docker, COINS GP est facilement déployable et maintenable
selon les standards professionnels.

yaml
Copier le code

---

## ✅ Maintenant c’est propre
- ✔️ Tu peux **copier-coller directement**
- ✔️ Les fichiers sont **cohérents entre eux**
- ✔️ Exploitables pour **rapport, soutenance, client**

👉 Prochaine étape possible :  
**MCD + schéma relationnel**, **diagrammes UML**, ou **code Laravel/React réel**.






