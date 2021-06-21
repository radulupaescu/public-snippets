<?php

namespace Examples\ActiveRecord;

class User extends ActiveRecord
{
    /**
     * The user id.
     *
     * @var int $id;
     */
    private int $id;

    /**
     * User full name.
     *
     * @var string $name
     */
    private string $name;

    /**
     * User email.
     *
     * @var string $email
     */
    private string $email;

    /**
     * User password.
     *
     * @var string $password
     */
    private string $password;

    /**
     * User constructor.
     * The default value for id is -1, signaling that the record hasn't been saved yet into the database.
     * @param int $id
     */
    public function __construct(int $id = -1)
    {
        $this->id = $id;
    }

    /**
     * Getter for user id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Getter user full name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setter for $name property. Will set the user full name and return the User object.
     *
     * @param string $name
     * @return User
     */
    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter for user email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Setter for $email property. Will set the user e-mail and return the User object.
     *
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Getter for user password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Setter for $password property. Will set the user full name and return the User object.
     * The password must be already hashed when calling the setter because this method is used when
     * retrieving the data from the database as well. If you hash it here, you'll find it hashes it again
     * when reading from the database.
     *
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(): bool
    {
        $params = [
            ':name' => $this->getName(),
            ':email' => $this->getEmail(),
            ':password' => $this->getPassword(),
        ];

        if ($this->getId() > 0) {
            // If the id is not -1 then this specific user is already in the database, so we UPDATE.
            $upsertQuery = '
                UPDATE `users`
                SET
                    name = :name,
                    email = :email, 
                    password = :password 
                WHERE
                    id = :id
            ';

            // Add the id to the parameter list, making sure this query can be executed.
            $params[':id'] = $this->getId();
        } else {
            // We're on the else statement because the user id is -1, so this is a new user, we must INSERT.
            $upsertQuery = '
                INSERT INTO `users`
                    (name, email, password)
                VALUES
                    (:name, :email, :password)
            ';
        }

        $query = self::$database->prepare($upsertQuery);
        try {
            $query->execute($params);
            if ($query->rowCount() > 0) { // Query had effect.
                if ($this->getId() == -1) { // current user was INSERTed into the db
                    // set the new id for this user
                    $this->id = self::$database->lastInsertId();
                }
            }
        } catch (\Exception $e) {
            // Should properly handle this.
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        $deleteQuery = 'DELETE FROM `users` WHERE id = :id';

        if ($this->getId() > 0) {
            $query = self::$database->prepare($deleteQuery);

            try {
                $query->execute(
                    [':id' => $this->getId()]
                );

                if ($query->rowCount() > 0) {
                    $this->id = -1;

                    return true;
                }
            } catch (\Exception $e) {
                // Here you should actually handle this or log it.
                return false;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    static public function findAll(): array
    {
        $users = [];
        $findQuery = 'SELECT * FROM `users`';

        try {
            $userList = self::$database->query($findQuery);

            foreach ($userList as $user) {
                $users[] = (new User($user['id']))
                                ->setName($user['name'])
                                ->setEmail($user['email'])
                                ->setPassword($user['password']);
            }
        } catch (\Exception $e) {
            // Should properly handle this.
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    static public function findById(int $id): ?User
    {
        $findQuery = 'SELECT * FROM `users` WHERE id=:id';
        $query = self::$database->prepare($findQuery);

        try {
            $query->execute([':id' => $id]);
            $userData = $query->fetch(\PDO::FETCH_ASSOC);

            return (new User($userData['id']))
                ->setName($userData['name'])
                ->setEmail($userData['email'])
                ->setPassword($userData['password']);
        } catch (\Exception $e) {
            // Should properly handle this.
        }

        return null;
    }

    public static function findByEmail($email)
    {
        $findQuery = 'SELECT * FROM `users` WHERE email=:email';
        $query = self::$database->prepare($findQuery);

        try {
            $query->execute([
                ':email' => $email
            ]);

            $userData = $query->fetch(\PDO::FETCH_ASSOC);

            return (new User($userData['id']))
                ->setName($userData['name'])
                ->setEmail($userData['email'])
                ->setPassword($userData['password']);
        } catch (\Exception $e) {
            // Should handle this properly.
        }

        return null;
    }

    public function __toString(): string
    {
        return $this->getName() . '(' . $this->id . '), ' . $this->email . ', ' . $this->password;
    }
}
