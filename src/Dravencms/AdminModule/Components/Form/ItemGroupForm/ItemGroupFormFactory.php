<?php declare(strict_types = 1);
/*
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301  USA
 */
namespace Dravencms\AdminModule\Components\Form\ItemGroupForm;



use Dravencms\Model\Form\Entities\Form;
use Dravencms\Model\Form\Entities\ItemGroup;

/**
 * Interface ItemGroupFormFactory
 * @package AdminModule\Components\Form
 */
interface ItemGroupFormFactory
{
    /**
     * @param Form $form
     * @param ItemGroup|null $itemGroup
     * @return ItemGroupForm
     */
    public function create(Form $form, ItemGroup $itemGroup = null): ItemGroupForm;
}