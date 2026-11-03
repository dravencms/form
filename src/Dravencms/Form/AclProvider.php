<?php declare(strict_types=1);

namespace Dravencms\Form;

use Dravencms\User\Acl\{AclPermission, AclProviderInterface};

final class AclProvider implements AclProviderInterface
{
    public function getPermissions(): iterable
    {
        yield new AclPermission('form', 'edit', 'form.acl.permissions.edit.label', resourceLabel: 'form.acl.resource');
        yield new AclPermission('form', 'delete', 'form.acl.permissions.delete.label', resourceLabel: 'form.acl.resource');
    }
}
