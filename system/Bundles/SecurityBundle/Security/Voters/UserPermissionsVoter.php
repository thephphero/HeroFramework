<?php


/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 19/11/17
 * Time: 21:33
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\SecurityBundle\Security\Voters;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Bundles\SecurityBundle\Security\Repositories\User;
use PDO;
use ReflectionClass;
class UserPermissionsVoter extends Voter {

    const READ = 'read';
    const WRITE = 'write';

    private $table='user_permissions';

    public function __construct()
    {

    }

    public function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::READ, self::WRITE))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof PermissionModuleInterface) {
            return false;
        }

        return true;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::READ:
                return $this->canRead($subject, $user);
            case self::WRITE:
                return $this->canWrite($subject, $user);


        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canRead(PermissionModuleInterface $module, User $user)
    {
        // if they can write, they can read
        if ($this->canWrite($module, $user)) {
            return true;
        }

        return $this->isAllowed('read',$user,$module);
    }

    private function canWrite(PermissionModuleInterface $module, User $user)
    {

        return $this->isAllowed('write',$user,$module);
    }

    private function isAllowed($attribute,User $user, PermissionModuleInterface $module){
return true;
        //User is super-admin
        if($user->isSuperAdmin()){
            return true;
        }
        //User is admin
        if($user->isAdmin()){
            return true;
        }

        $reflect = new ReflectionClass($module);
        $moduleClass = $reflect->getShortName();

        $statement = $this->db->prepare("SELECT role FROM ".$this->table." WHERE region=:region AND project_id=:project_id AND attribute=:attribute AND class=:class");

        $statement->bindParam(':attribute', $attribute, PDO::PARAM_STR);
        $statement->bindValue(':class', $moduleClass, PDO::PARAM_STR);
        $statement->execute();

        if (!$rows = $statement->fetchAll(PDO::FETCH_ASSOC)) {
            return false;
        }

        if(is_array($rows)){
            foreach ($rows as $row){
                if(in_array($row['role'],$user->getRoles())){
                    return true;
                }
            }
        }

        return false;
    }

}