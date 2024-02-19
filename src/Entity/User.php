<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    #[Assert\NotBlank(message: "Le username de l'utilisateur est obligatoire")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Le username doit faire au moins {{ limit }} caractères", maxMessage: "Le username ne peut pas faire plus de {{ limit }} caractères")]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'email de l'utilisateur est obligatoire")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas une adresse email valide.")]
    #[Groups(["getUsers"])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mot de passe de l'utilisateur est obligatoire")]
    #[Assert\Length(min: 6, minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères")]
    private ?string $password = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Au moins un rôle doit être attribué à l'utilisateur")]
    private array $roles = [];

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["getUsers"])]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }
}
