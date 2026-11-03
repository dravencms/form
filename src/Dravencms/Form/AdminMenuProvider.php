<?php declare(strict_types=1);

namespace Dravencms\Form;

use Dravencms\Admin\Menu\{AdminMenuItem, AdminMenuProviderInterface};

final class AdminMenuProvider implements AdminMenuProviderInterface
{
    public function getItems(): iterable
    {
        yield new AdminMenuItem('form.overview', 'form.menu.overview', destination: ':Admin:Form:Form:default', parent: 'content', icon: 'fa-credit-card', order: 40, resource: 'form', privilege: 'edit', activeDestinations: [':Admin:Form:Item:*', ':Admin:Form:ItemGroup:*', ':Admin:Form:ItemOption:*']);
    }
}
