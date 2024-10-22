<?php
// src/Twig/AppExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;



use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use App\NCUO\OivBundle\Controller\ErrorResponseGenerator;
use App\NCUO\OivBundle\Entity\ORMObjectMetadata;
use App\NCUO\OivBundle\Entity\Oiv;
use App\NCUO\OivBundle\Entity\Section;
use App\NCUO\OivBundle\Entity\Field;
use Doctrine\ORM\EntityRepository;
//use App\NCUO\OivBundle\Form\OivType;
//use App\NCUO\PortalBundle\Entity\User;


use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;



class AppExtension extends AbstractExtension
{
    private $entityManager;
    private $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }
    public function getFunctions()
    {
        return [
            new TwigFunction('composeMenu', [$this, 'composeMenu']),
            new TwigFunction('composeLightMenu', [$this, 'composeLightMenu']),
        ];
    }

    public function composeMenu($id_role)
    {
        $arMenu = array();
        $conn = $this->entityManager->getConnection();

        // $user = $this->entityManager->getRepository(User::class)->findAll();
/*
        $sql = '
            SELECT mi.*, mip.parent_id as isParent
            FROM cms.menu_items as mi
            LEFT JOIN cms.menu_roles as mr ON mi.id=mr.menu_id
            LEFT JOIN cms.roles as r ON r.id=mr.role_id
            LEFT JOIN cms.users as u ON u.role=r.id
            LEFT JOIN ( SELECT parent_id FROM cms.menu_items WHERE parent_id IS NOT NULL GROUP BY parent_id ) AS mip ON mip.parent_id=mi.id
            WHERE 
            u.username = :login
            ORDER BY mi.item_position ASC
        ';
*/
        $sql = '
            SELECT mi.*, mip.parent_id as isParent
            FROM cms.menu_items as mi
            LEFT JOIN cms.menu_roles as mr ON mi.id=mr.menu_id
            LEFT JOIN cms.roles as r ON r.id=mr.role_id
            LEFT JOIN ( SELECT parent_id FROM cms.menu_items WHERE parent_id IS NOT NULL GROUP BY parent_id ) AS mip ON mip.parent_id=mi.id
            WHERE 
            r.id = :id_role
            ORDER BY mi.item_position ASC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id_role'=>$id_role]);
        $listPages = $stmt->fetchAll();

        $arMMenu = array();
        $arSubMenu = array();
        foreach ($listPages as $arPage) {
            $arPage['url_alias'] = str_replace(array('/public/index.php', '/public', '/'), '', $arPage['url']);
            if( intval($arPage['parent_id']) > 0 ){
                $arSubMenu[$arPage['parent_id']][] = $arPage;
            }else{
                $arMMenu[] = $arPage;
            }
        }

        foreach ($arMMenu as $arPage) {
            foreach ($arSubMenu as $key => $value) {
                if($key == $arPage["id"] ){
                    $arPage["subMenu"] = $arSubMenu[$arPage['id']];
                }
            }
            $arMenu[] = $arPage;
        }

/*        foreach ($arMenu as $arPage) {
            echo "<br>{$arPage['item_name']} {$arPage['parent_id']}";
            if( isset($arPage["subMenu"]) ){
                foreach ($arPage["subMenu"] as $key => $arSubPage) {
                    echo "<br>{$arSubPage['item_name']}";
                }
            }
        }*/

        $arMenuTemplate = $this->templateMenu($arMenu);

        return $arMenuTemplate;
    }

    public function templateMenu($arMenu)
    {
        $symfRoute = $this->requestStack->getMasterRequest()->getPathInfo();
        $symfRoute = str_replace('/', '', $symfRoute);
        
        $arMenuTemplate = '
        <ul class="nav menu-left" id="side-menu">';

        foreach ($arMenu as $arPage) {
            // echo "<br>{$arPage['item_name']} {$arPage['parent_id']}";
            if( isset($arPage["subMenu"]) ){
                $arMenuTemplate .= '
            <li>
                <a href="#">
                    <span class="fa arrow"></span>
                    <div class="smi-title">'.$arPage['item_name'].'</div>
                </a>
                
                <ul class="nav nav-second-level nav-n-level">';
                    foreach ($arPage["subMenu"] as $key => $arSubPage) {
                        $arMenuTemplate .= '
                    <li>
                            <a href="'.$arSubPage['url'].'" '.(strpos($arSubPage['url'], 'http') !== false ? 'target="_blank"' : null).'>
                                <div class="smi-title">'.$arSubPage['item_name'].'</div>
                            </a>
                    </li>';
                    }
                    $arMenuTemplate .= '
                </ul>
            </li>';
            }else{
                $arMenuTemplate .= '
            <li class="noChildren">
                <a href="'.$arPage['url'].'" '.(strpos($arPage['url'], 'http') !== false ? 'target="_blank"' : null).'>
                   <div class="smi-title">'.$arPage['item_name'].'</div>
                </a>
            </li>';

            }
        }

        $arMenuTemplate .= '
        </ul>';

        return $arMenuTemplate;
    }


    public function composeLightMenu($id_role, $username)
    {
        $arMenu = array();
        $conn = $this->entityManager->getConnection();

        $sql = '
            SELECT mi.*, mip.parent_id as isParent
            FROM cms.menu_items as mi
            LEFT JOIN cms.menu_roles as mr ON mi.id=mr.menu_id
            LEFT JOIN cms.roles as r ON r.id=mr.role_id
            LEFT JOIN ( SELECT parent_id FROM cms.menu_items WHERE parent_id IS NOT NULL GROUP BY parent_id ) AS mip ON mip.parent_id=mi.id
            WHERE 
            r.id = :id_role
            ORDER BY mi.item_position ASC
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id_role'=>$id_role]);
        $listPages = $stmt->fetchAll();

        $arMMenu = array();
        $arSubMenu = array();
        foreach ($listPages as $arPage) {
            $arPage['url'] = str_replace('?ru=', '?ru='.$username, $arPage['url']);
            $arPage['url_alias'] = str_replace(array('/public/index.php', '/public', '/'), '', $arPage['url']);
            if( intval($arPage['parent_id']) > 0 ){
                $arSubMenu[$arPage['parent_id']][] = $arPage;
            }else{
                $arMMenu[] = $arPage;
            }
        }

        $cnt = 0;
        $cntRows = 0;
        $itemsInRow = 3;
        $arSubTmpMenu = array();
        foreach ($arMMenu as $arPage) {
            if ( ($cnt != 0) && ($cnt % $itemsInRow == 0) ) {
                //$arMenu[$cntRows]['subMenu'] = $arSubTmpMenu;
                $cntRows++;
                $arSubTmpMenu = array();
            }
            $cnt++;
            foreach ($arSubMenu as $key => $value) {
                if($key == $arPage["id"] ){
                    $arSubTmpMenu[] = $arSubMenu[$arPage['id']];
                }
            }
            // echo ($cnt.'='.$cntRows);
            // echo ('<br> ');
            $arMenu[$cntRows][] = $arPage;
        }


        $arMenuTemplate = $this->templateLightMenu($arMenu, $arSubMenu);

        return $arMenuTemplate;
    }

    public function templateLightMenu($arMenu, $arSubMenu)
    {
        $symfRoute = $this->requestStack->getMasterRequest()->getPathInfo();
        $symfRoute = str_replace('/', '', $symfRoute);
        /*
        $arMenuTemplate = '
      <div style="margin: 0px auto; margin-top: 5%; max-width: 1180px">
        <div class="menu-plate" onclick="manageSubmenu(\'SubMenuLine1\', this)"><span class="asPointer">Паспорта</span></div>
        <div class="menu-plate" onclick="manageSubmenu(\'SubMenuLine2\', this)"><span class="asPointer">Контроль управления функционированием</span></div>
        <div class="menu-plate"><a href="">ГИС МО</a></div>
      </div>
      <div id="SubMenuLine1" class="sub-menu" style="margin: 0px auto; display: none; max-width: 1180px">
        <div class="submenu-plate"><a href="/public/index.php/oiv">Паспорта ФОИВ РФ</a></div>
        <div class="submenu-plate"><a href="/public/index.php/region">Паспорта Субъектов РФ</a></div>
        <div class="submenu-plate"><a href="/public/index.php/search">Поиск в паспортах ФОИВ РФ и Субъектов РФ</a></div>
      </div>
      <div id="SubMenuLine2" class="sub-menu" style="margin: 0px auto; display: none; max-width: 1180px">
        <div class="submenu-plate"><a href="/public/index.php/admin/menu">Администрирование меню</a></div>
        <div class="submenu-plate"><a href="/public/index.php/admin/users">Администрирование пользователей</a></div>
        <div class="submenu-plate"><a href="/public/index.php/admin/roles">Администрирование ролей</a></div>
        <div class="submenu-plate"><a href="/public/index.php/func/rep_data_act_control">Актуализация данных</a></div>
        <div class="submenu-plate"><a href="/public/index.php/map/adm/map">Выгрузка в ГИС</a></div>
        <div class="submenu-plate"><a href="/public/index.php/eif/sources">Данные</a></div>
        <div class="submenu-plate"><a href="/public/index.php/foiv/0/foiv_systems">Информационные системы</a></div>
        <div class="submenu-plate"><a href="/public/index.php/service/services">Регламентные задачи</a></div>
      </div>
      <div style="margin: 0px auto; float: left">
        <div class="menu-plate"><a href="">Электронная почта</a></div>
        <div class="menu-plate"><a href="">FTP</a></div>
        <div class="menu-plate plate-fake"></div>
      </div>';

*/

        $arMenuTemplate = '';
        $arParentId = array();
        foreach ($arMenu as $k => $arRows) {
            $arMenuTemplate .= '<div style="margin: 0px auto; '.($k == 0 ? ' margin-top: 5%; ' : '').' max-width: 1180px">';
            foreach ($arRows as $key => $arPage) {
                if( empty($arSubMenu[$arPage['id']]) ) {
                    if(strpos($arPage['url'], 'http') !== false){
                        $arMenuTemplate .= '<div class="menu-plate"><a href="'.$arPage['url'].'" '.(strpos($arPage['url'], 'http') !== false ? 'target="_blank"' : null).'>'.$arPage['item_name'].'</a></div>';
                    }else{
                        $arMenuTemplate .= '<div class="menu-plate"><a href="'.$arPage['url'].'" '.(strpos($arPage['url'], 'http') !== false ? 'target="_blank"' : null).'>'.$arPage['item_name'].'</a></div>';
                    }
                }else{
                    $arParentId[] = $arPage['id'];
                    $arMenuTemplate .= '<div class="menu-plate" onclick="manageSubmenu(\'SubMenuLine'.$arPage['id'].'\', this)"><span class="asPointer">'.$arPage['item_name'].'</span></div>';
                }
            }
            $arMenuTemplate .= "</div>";
            if( count($arParentId) > 0 ){
                foreach ($arParentId as $parentId ) {
                    $arMenuTemplate .= '<div id="SubMenuLine'.$parentId.'" class="sub-menu" style="margin: 0px auto; display: none; max-width: 1180px">';
                    foreach ($arSubMenu[$parentId] as $arPage) {
                        $arMenuTemplate .= '<div class="submenu-plate"><a href="'.$arPage['url'].'" '.(strpos($arPage['url'], 'http') !== false ? 'target="_blank"' : null).'>'.$arPage['item_name'].'</a></div>';
                    }
                    $arMenuTemplate .= "</div>";
                }

                $arParentId = array();
            }
        }

        return $arMenuTemplate;
    }

}
?>