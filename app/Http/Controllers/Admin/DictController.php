<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DictItem;
use App\Models\DictType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DictController extends Controller
{
    public function index(): View
    {
        return view('admin.dicts.index');
    }

    public function typeList(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 15), 1), 100);
        $keyword = trim((string) $request->input('keyword', ''));

        $query = DictType::query()->withCount('items')->orderBy('id');
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        $page = $query->paginate($limit);

        return response()->json([
            'code' => 0,
            'msg' => '',
            'count' => $page->total(),
            'data' => $page->getCollection()->map(fn (DictType $t) => [
                'id' => $t->id,
                'name' => $t->name,
                'code' => $t->code,
                'items_count' => $t->items_count,
                'status' => $t->status,
                'remark' => $t->remark ?? '',
                'updated_at' => $t->updated_at?->format('Y-m-d H:i') ?? '-',
            ])->values(),
        ]);
    }

    public function createType(): View
    {
        return view('admin.dicts.type_form', ['dictType' => null]);
    }

    public function storeType(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:dict_types,code'],
            'status' => ['required', 'in:0,1'],
            'remark' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => '请输入字典名称',
            'code.required' => '请输入字典编码',
            'code.unique' => '字典编码已存在',
        ]);

        DictType::query()->create($data);

        return response()->json(['code' => 0, 'message' => '字典类型创建成功']);
    }

    public function editType(DictType $dictType): View
    {
        return view('admin.dicts.type_form', ['dictType' => $dictType]);
    }

    public function updateType(Request $request, DictType $dictType): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('dict_types', 'code')->ignore($dictType->id)],
            'status' => ['required', 'in:0,1'],
            'remark' => ['nullable', 'string', 'max:255'],
        ], [
            'code.unique' => '字典编码已存在',
        ]);

        $dictType->update($data);

        return response()->json(['code' => 0, 'message' => '字典类型已更新']);
    }

    public function destroyType(DictType $dictType): JsonResponse
    {
        $dictType->delete();

        return response()->json(['code' => 0, 'message' => '字典类型及其明细项已删除']);
    }

    public function itemList(DictType $dictType): JsonResponse
    {
        $items = $dictType->items()->get();

        return response()->json([
            'code' => 0,
            'msg' => '',
            'count' => $items->count(),
            'data' => $items->map(fn (DictItem $i) => [
                'id' => $i->id,
                'dict_type_id' => $i->dict_type_id,
                'label' => $i->label,
                'value' => $i->value,
                'color' => $i->color,
                'sort' => $i->sort,
                'status' => $i->status,
                'remark' => $i->remark ?? '',
            ])->values(),
        ]);
    }

    public function createItem(DictType $dictType): View
    {
        return view('admin.dicts.item_form', ['dictType' => $dictType, 'item' => null]);
    }

    public function storeItem(Request $request, DictType $dictType): JsonResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'value' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dict_items', 'value')->where('dict_type_id', $dictType->id),
            ],
            'color' => ['required', 'in:teal,green,amber,blue,red,gray'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
            'remark' => ['nullable', 'string', 'max:255'],
        ], [
            'label.required' => '请输入字典标签',
            'value.required' => '请输入字典键值',
            'value.unique' => '当前字典类型下该键值已存在',
        ]);

        $dictType->items()->create($data);

        return response()->json(['code' => 0, 'message' => '字典项创建成功']);
    }

    public function editItem(DictItem $dictItem): View
    {
        return view('admin.dicts.item_form', [
            'dictType' => $dictItem->dictType,
            'item' => $dictItem,
        ]);
    }

    public function updateItem(Request $request, DictItem $dictItem): JsonResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'value' => [
                'required',
                'string',
                'max:100',
                Rule::unique('dict_items', 'value')
                    ->where('dict_type_id', $dictItem->dict_type_id)
                    ->ignore($dictItem->id),
            ],
            'color' => ['required', 'in:teal,green,amber,blue,red,gray'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:0,1'],
            'remark' => ['nullable', 'string', 'max:255'],
        ], [
            'value.unique' => '当前字典类型下该键值已存在',
        ]);

        $dictItem->update($data);

        return response()->json(['code' => 0, 'message' => '字典项已更新']);
    }

    public function destroyItem(DictItem $dictItem): JsonResponse
    {
        $dictItem->delete();

        return response()->json(['code' => 0, 'message' => '字典项已删除']);
    }
}
