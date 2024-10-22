<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`users`", schema="cms")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $role;

    /**
     * @ORM\Column(type="integer", length=255)
     */
    private $foiv;

    
    private $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $oiv_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $middlename;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastname;

    private $roleDescription;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user_email_login;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user_email_pass;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user_ftp_login;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $user_ftp_pass;

    /**
     * @ORM\Column(type="smallint")
     */
    private $user_email_autologin;

    /**
     * @ORM\Column(type="smallint")
     */
    private $user_ftp_autologin;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getRole(): string
    {
        return (string) $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        // guarantee every user at least has ROLE_USER
/*
1;"ROLE_ADMIN"
2;"ROLE_NCUO"
3;"ROLE_FOIV"
5;"ROLE_VDL"
6;"ROLE_USER"

*/

        switch ($this->role) :

            case 1:
                $roles[] = "ROLE_ADMIN";
            break;
            case 2:
                $roles[] = "ROLE_NCUO";
            break;
            case 3:
                $roles[] = "ROLE_FOIV";
            break;
            case 5:
                $roles[] = "ROLE_VDL";
            break;
            case 6:
                $roles[] = "ROLE_ROIV";
            break;
            default:
                $roles[] = "ROLE_USER";
            break;
        endswitch;
        array_unique($roles);

        return $roles;
    }

    public function setRoles($role_name) 
    {
        $this->roles = [$role_name];
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getFoiv()
    {
        return  $this->foiv;
    }

    public function setFoiv(  $foiv)
    {
        $this->foiv = $foiv;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getOivId()
    {
        return $this->oiv_id;
    }

    public function setOivId($oiv_id)
    {
        $this->oiv_id = $oiv_id;

        return $this;
    }


    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    public function setMiddlename(?string $middlename): self
    {
        $this->middlename = $middlename;

        return $this;
    }

    public function getRoleDescription(): ?string
    {
        return $this->roleDescription;
    }

    public function setRoleDescription(?string $roleDescription): self
    {
        $this->roleDescription = $roleDescription;

        return $this;
    }

    public function getUserEmailLogin(): ?string
    {
        return $this->user_email_login;
    }

    public function setUserEmailLogin(?string $str): self
    {
        $this->user_email_login = $str;

        return $this;
    }

    public function getUserEmailLoginShort(): ?string
    {
        return substr($this->user_email_login, 0, strpos($this->user_email_login, '@'));
    }

    public function getUserEmailPass(): ?string
    {
        return base64_decode($this->user_email_pass);
    }

    public function setUserEmailPass(?string $str): self
    {
        $this->user_email_pass = base64_encode($str);

        return $this;
    }

    public function getUserFtpLogin(): ?string
    {
        return $this->user_ftp_login;
    }

    public function setUserFtpLogin(?string $str): self
    {
        $this->user_ftp_login = $str;

        return $this;
    }

    public function getUserFtpPass(): ?string
    {
        return base64_decode($this->user_ftp_pass);
    }

    public function setUserFtpPass(?string $str): self
    {
        $this->user_ftp_pass = base64_encode($str);

        return $this;
    }

    public function getUserEmailAutologin()
    {
        return intval($this->user_email_autologin);
    }

    public function setUserEmailAutologin( $str): self
    {
        $this->user_email_autologin = $str;

        return $this;
    }

    public function getUserFtpAutologin()
    {
        return intval($this->user_ftp_autologin);
    }

    public function setUserFtpAutologin($str): self
    {
        $this->user_ftp_autologin = $str;

        return $this;
    }

}
