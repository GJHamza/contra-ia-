# API Backend Laravel – Documentation pour intégration React

## 1. Authentification
- `POST /api/register` : inscription
- `POST /api/login` : connexion (retourne un token)
- `POST /api/logout` : déconnexion (token requis)

## 2. Documents
- `GET /api/documents` : liste paginée
- `POST /api/documents` : création
- `GET /api/documents/{id}` : détail
- `PUT /api/documents/{id}` : mise à jour
- `DELETE /api/documents/{id}` : suppression
- `GET /api/documents/{id}/download` : téléchargement PDF
- `GET /api/documents/{id}/pdf` : PDF direct

## 3. Génération IA
- `POST /api/generer-texte` : génère un texte juridique avec IA
  - Body : `{ "prompt": "Rédige un contrat..." }`
  - Retour : `{ success, generated_text }`

## 4. Containers, Clauses, Templates, etc.
- `api/containers`, `api/clauses`, `api/clause-types`, `api/templates` : CRUD complet

## 5. Authentification des requêtes
- Toutes les routes protégées nécessitent le header :
  - `Authorization: Bearer {token}`

## 6. CORS
- CORS activé pour `http://localhost:3000` (React par défaut)

## 7. Erreurs
- Les erreurs sont retournées en JSON avec un code HTTP adapté (401, 403, 422, 500...)

## 8. Exemple de login (POST)
```json
{
  "email": "test@example.com",
  "password": "password"
}
```

## 9. Tester l’API
- Utilise Postman, Insomnia ou fetch/axios côté React.

---

**Backend prêt pour intégration avec n’importe quel frontend moderne (React, Vue, mobile, etc.)**
