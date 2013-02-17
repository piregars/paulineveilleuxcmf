<?php

namespace Msi\Bundle\CmfBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    protected $sidebarMenu;

    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getAdminMenu($factory);

        $menu->setChildrenAttribute('class', 'nav');
        $this->setDropdownMenuAttributes($menu);

        foreach ($menu as $row) {
            $this->checkRole($row);
            if ($row->hasChildren()) {
                $row->setExtra('safe_label', true);
                $row->setLabel($row->getName().' <b class="caret"></b>');
            }
        }

        return $menu;
    }

    public function sidebarMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getAdminMenu($factory);
        $this->findCurrent($menu);

        if (!$this->sidebarMenu || $this->sidebarMenu === $menu) {
            return $factory->createItem('default');
        }

        $this->sidebarMenu->setChildrenAttribute('class', 'nav nav-tabs nav-stacked');
        $this->setDropdownSubmenuAttributes($this->sidebarMenu);

        foreach ($menu as $row) {
            $this->checkRole($row);
        }

        return $this->sidebarMenu;
    }

    protected function getAdminMenu($factory)
    {
        $root = $this->container->get('msi_cmf.menu_root_manager')->findRootByName('admin', $this->container->get('request')->getLocale());

        if (!$root) {
            return $factory->createItem('default');
        }

        $menu = $factory->createFromNode($root);
        if (!$menu->getExtra('published')) {
            foreach ($menu->getChildren() as $child) {
                $menu->removeChild($child);
            }
        }
        $this->removeUnpublished($menu);

        return $menu;
    }

    protected function setDropdownMenuAttributes($menuItem)
    {
        foreach ($menuItem->getChildren() as $child) {
            $this->checkRole($child);
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown');
                $child->setLinkAttribute('class', 'dropdown-toggle');
                $child->setLinkAttribute('data-toggle', 'dropdown');
                $child->setChildrenAttribute('class', 'dropdown-menu');
            }
            $this->setDropdownSubmenuAttributes($child);
        }
    }

    protected function setDropdownSubmenuAttributes($menuItem)
    {
        foreach ($menuItem->getChildren() as $child) {
            $this->checkRole($child);
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown-submenu');
                $child->setChildrenAttribute('class', 'dropdown-menu');
                $child->setLinkAttribute('tabindex', -1);
            }
        }
    }

    protected function findCurrent($node)
    {
        $requestUri = $this->container->get('request')->getRequestUri();
        if ($pos = strrpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        foreach ($node->getChildren() as $child) {
            $menuUri = $child->getUri();
            if ($menuUri === $requestUri) {
                $child->setCurrent(true);
                $this->sidebarMenu = $child->getParent();
            } else {
                $child->setCurrent(false);
                $this->findCurrent($child);
            }
        }
    }

    protected function checkRole($menu)
    {
        if (!$menu->getExtra('groups')->count()) {
            return;
        }

        foreach ($menu->getExtra('groups') as $group) {
            if ($this->container->get('security.context')->getToken()->getUser()->getGroups()->contains($group)) {
                return;
            }
        }

        $menu->getParent()->removeChild($menu);
    }

    public function removeUnpublished($node)
    {
        foreach ($node->getChildren() as $child) {
            if ($child->hasChildren()) {
                $this->removeUnpublished($child);
            }
            if (!$child->getExtra('published')) {
                $child->getParent()->removeChild($child);
            }
        }
    }
}
