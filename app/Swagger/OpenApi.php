<?php

/**
 * @OA\Info(
 *     title="API Bancaire RESTful",
 *     version="1.0.0",
 *     description="API RESTful pour la gestion des comptes bancaires",
 *     @OA\Contact(
 *         email="support@banque.example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://api.banque.example.com/api/v1",
 *     description="Serveur de production"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur de développement local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="CompteResource",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
 *     @OA\Property(property="numeroCompte", type="string", example="C00123456"),
 *     @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
 *     @OA\Property(property="type", type="string", enum={"cheque","epargne","courant"}, example="epargne"),
 *     @OA\Property(property="solde", type="number", format="float", example=1250000),
 *     @OA\Property(property="devise", type="string", example="FCFA"),
 *     @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
 *     @OA\Property(property="statut", type="string", enum={"actif","inactif","suspendu","bloque","ferme"}, example="actif"),
 *     @OA\Property(
 *         property="metadata",
 *         type="object",
 *         @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
 *         @OA\Property(property="version", type="integer", example=1)
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="currentPage", type="integer", example=1),
 *     @OA\Property(property="totalPages", type="integer", example=3),
 *     @OA\Property(property="totalItems", type="integer", example=25),
 *     @OA\Property(property="itemsPerPage", type="integer", example=10),
 *     @OA\Property(property="hasNext", type="boolean", example=true),
 *     @OA\Property(property="hasPrevious", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="self", type="string", example="/api/v1/comptes?page=1&limit=10"),
 *     @OA\Property(property="next", type="string", example="/api/v1/comptes?page=2&limit=10"),
 *     @OA\Property(property="first", type="string", example="/api/v1/comptes?page=1&limit=10"),
 *     @OA\Property(property="last", type="string", example="/api/v1/comptes?page=3&limit=10")
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta"),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Erreur de validation"),
 *     @OA\Property(property="errors", type="object")
 * )
 *
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
