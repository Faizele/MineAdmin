<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Http\Admin\Controller\Permission;

use App\Http\Admin\Controller\AbstractController;
use App\Http\Admin\Middleware\PermissionMiddleware;
use App\Http\Admin\Request\Permission\MenuRequest;
use App\Http\Common\Middleware\AccessTokenMiddleware;
use App\Http\Common\Middleware\OperationMiddleware;
use App\Http\Common\Result;
use App\Service\Permission\MenuService;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Swagger\Annotation\Delete;
use Hyperf\Swagger\Annotation\Get;
use Hyperf\Swagger\Annotation\HyperfServer;
use Hyperf\Swagger\Annotation\JsonContent;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Annotation\Put;
use Hyperf\Swagger\Annotation\RequestBody;
use Mine\Kernel\Access\Attribute\Permission;
use Mine\Kernel\Core\CurrentUser;
use Mine\Kernel\Swagger\Attributes\PageResponse;
use Mine\Kernel\Swagger\Attributes\ResultResponse;

#[HyperfServer(name: 'http')]
#[Middleware(middleware: AccessTokenMiddleware::class, priority: 100)]
#[Middleware(middleware: PermissionMiddleware::class, priority: 99)]
#[Middleware(middleware: OperationMiddleware::class, priority: 98)]
final class MenuController extends AbstractController
{
    public function __construct(
        private readonly MenuService $service,
        private readonly CurrentUser $user
    ) {}

    #[Get(
        path: '/admin/menu/list',
        operationId: 'menuList',
        summary: '菜单列表',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['菜单管理']
    )]
    #[Permission(code: 'menu:list')]
    #[ResultResponse(instance: new Result())]
    public function pageList(RequestInterface $request): Result
    {
        return $this->success(data: $this->service->getRepository()->list([
            'children' => true,
            'parent_id' => 0,
        ]));
    }

    #[Post(
        path: '/admin/menu',
        operationId: 'menuCreate',
        summary: '创建菜单',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['菜单管理']
    )]
    #[RequestBody(
        content: new JsonContent(ref: MenuRequest::class, title: '创建菜单')
    )]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'menu:create')]
    public function create(MenuRequest $request): Result
    {
        $this->service->create(array_merge($request->validated(), [
            'created_by' => $this->user->id(),
        ]));
        return $this->success();
    }

    #[Put(
        path: '/admin/menu/{id}',
        operationId: 'menuEdit',
        summary: '编辑菜单',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['菜单管理']
    )]
    #[RequestBody(
        content: new JsonContent(ref: MenuRequest::class, title: '编辑菜单')
    )]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'menu:save')]
    public function save(int $id, MenuRequest $request): Result
    {
        $this->service->updateById($id, array_merge($request->validated(), [
            'updated_by' => $this->user->id(),
        ]));
        return $this->success();
    }

    #[Delete(
        path: '/admin/menu',
        operationId: 'menuDelete',
        summary: '删除菜单',
        security: [['Bearer' => [], 'ApiKey' => []]],
        tags: ['菜单管理']
    )]
    #[PageResponse(instance: new Result())]
    #[Permission(code: 'menu:delete')]
    public function delete(RequestInterface $request): Result
    {
        $this->service->deleteById($request->all(), false);
        return $this->success();
    }
}