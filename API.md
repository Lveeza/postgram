# Postagram API Documentation

A RESTful API for Postagram, an Instagram-style social posting application. Authentication is handled via [Laravel Sanctum](https://laravel.com/docs/sanctum) using bearer tokens.

**Base URL:** `https://postgram-production-8430.up.railway.app/api/posts`

---

## Authentication

### Register

Creates a new user and returns an API token.

```
POST /register
```

**Body**

```json
{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123"
}
```

**Response `201`**

```json
{
    "message": "Registration successful",
    "token": "1|abcdef123456...",
    "user": {
        "id": 1,
        "name": "Jane Doe",
        "email": "jane@example.com"
    }
}
```

Rate limited to **5 requests/minute**.

---

### Login

Authenticates a user and returns an API token.

```
POST /login
```

**Body**

```json
{
    "email": "jane@example.com",
    "password": "password123"
}
```

**Response `200`**

```json
{
    "message": "Login successful!",
    "access_token": "2|xyz789...",
    "token_type": "Bearer"
}
```

**Response `401`** — invalid credentials

```json
{ "message": "Invalid credentials" }
```

Rate limited to **5 requests/minute**.

---

### Logout

Revokes the token used for the current request.

```
POST /logout
```

**Headers:** `Authorization: Bearer {token}`

**Response `200`**

```json
{ "message": "Logged out successfully" }
```

---

## Authentication Header

All protected routes require:

```
Authorization: Bearer {your_token}
```

Protected routes are also rate limited to **60 requests/minute** per user.

---

## Posts

### List Posts

```
GET /posts
```

**Query Parameters**
| Param | Type | Description |
|---|---|---|
| `search` | string | Optional. Filters posts by title or body. |
| `page` | int | Optional. Pagination page number. |

**Response `200`**

```json
{
    "data": [
        {
            "id": 1,
            "title": "My first post",
            "body": "...",
            "user": { "id": 1, "name": "Jane Doe" }
        }
    ],
    "links": { "...": "..." },
    "meta": { "...": "..." }
}
```

---

### Show a Post

```
GET /posts/{id}
```

Returns a single post with its comments and comment authors loaded.

**Response `200`**

```json
{
    "data": {
        "id": 1,
        "title": "My first post",
        "body": "...",
        "comments": [
            { "id": 5, "content": "Nice!", "user": { "id": 2, "name": "Rubi" } }
        ]
    }
}
```

---

### Create a Post

```
POST /posts
```

**Headers:** `Authorization: Bearer {token}` — token must have the `posts:create` ability.

**Body** (`multipart/form-data` if including an image)
| Field | Type | Required |
|---|---|---|
| `title` | string | Yes |
| `body` | string | Yes |
| `image` | file (jpeg/jpg/png/gif, max 2MB) | No |

**Response `201`**

```json
{
    "message": "Post created successfully!",
    "post": { "id": 10, "title": "...", "body": "..." }
}
```

**Response `403`** — missing token ability

```json
{ "message": "Token does not have permission to create posts." }
```

---

### Update a Post

```
PUT /posts/{id}
```

**Headers:** `Authorization: Bearer {token}` — token must have `posts:update` ability, and the user must own the post.

**Body**

```json
{
    "title": "Updated title",
    "body": "Updated body"
}
```

**Response `200`**

```json
{
    "message": "Post updated successfully!",
    "post": { "id": 10, "title": "Updated title", "body": "Updated body" }
}
```

**Response `403`** — not the owner, or missing token ability.

---

### Delete a Post

```
DELETE /posts/{id}
```

**Headers:** `Authorization: Bearer {token}` — token must have `posts:delete` ability, and the user must own the post.

**Response `200`**

```json
{ "message": "Post deleted successfully!" }
```

---

## Comments

### Add a Comment

```
POST /posts/{postId}/comments
```

**Headers:** `Authorization: Bearer {token}`

**Body**

```json
{ "content": "Great post!" }
```

**Response `201`**

```json
{
    "message": "Comment added successfully!",
    "comment": {
        "id": 3,
        "content": "Great post!",
        "user": { "id": 2, "name": "Rubi" }
    }
}
```

> Triggers a `CommentCreated` event, which queues an email notification to the post owner.

---

### Update a Comment

```
PUT /comments/{id}
```

**Headers:** `Authorization: Bearer {token}` — user must own the comment.

**Body**

```json
{ "content": "Updated comment text" }
```

**Response `200`**

```json
{
    "message": "Comment updated successfully!",
    "comment": { "id": 3, "content": "Updated comment text" }
}
```

---

### Delete a Comment

```
DELETE /comments/{id}
```

**Headers:** `Authorization: Bearer {token}` — user must own the comment.

**Response `200`**

```json
{ "message": "Comment deleted successfully!" }
```

---

## Error Responses

| Status | Meaning                                                                                |
| ------ | -------------------------------------------------------------------------------------- |
| `401`  | Missing/invalid token, or invalid login credentials                                    |
| `403`  | Authenticated, but not authorized (not the owner, or token lacks the required ability) |
| `422`  | Validation failed — response body includes an `errors` object per field                |
| `429`  | Rate limit exceeded — `5/min` on auth routes, `60/min` on authenticated routes         |

**Example `422`**

```json
{
    "message": "The title field is required.",
    "errors": {
        "title": ["The title field is required."]
    }
}
```

---

## Notes on Architecture

- **Authorization** is enforced via Laravel Policies (`PostPolicy`, `CommentPolicy`) — the same policies used by the web application, ensuring consistent rules across both.
- **Token abilities** (`posts:create`, `posts:update`, `posts:delete`) provide an additional scoping layer on top of ownership checks, useful for issuing limited-scope tokens (e.g., a read-only integration).
- **Database transactions** wrap post creation/deletion to keep the `posts_count` counter on `User` in sync with actual post records.
- **Events & queued listeners** handle side effects (email notifications) outside the request/response cycle, keeping API responses fast.
