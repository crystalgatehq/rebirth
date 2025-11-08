<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display a listing of the settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Setting::query();

        // Filter by group if provided
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by sort_order by default
        $settings = $query->orderBy('group')
                         ->orderBy('sort_order')
                         ->get()
                         ->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Store a newly created setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item' => 'required|string|max:255|unique:settings,item',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'current_value' => 'nullable|string',
            'data_type' => 'required|in:string,integer,boolean,double,array,object',
            'group' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_public' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $setting = Setting::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully',
            'data' => $setting,
        ], 201);
    }

    /**
     * Display the specified setting.
     *
     * @param  string  $item
     * @return \Illuminate\Http\Response
     */
    public function show($item)
    {
        $setting = Setting::where('item', $item)->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => $setting,
        ]);
    }

    /**
     * Update the specified setting in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $item)
    {
        $setting = Setting::where('item', $item)->firstOrFail();

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'default_value' => 'nullable|string',
            'current_value' => 'nullable|string',
            'data_type' => 'sometimes|in:string,integer,boolean,double,array,object',
            'group' => 'sometimes|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_public' => 'boolean',
            'options' => 'nullable|array',
        ]);

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Update a setting value by item name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $item
     * @return \Illuminate\Http\Response
     */
    public function updateValue(Request $request, $item)
    {
        $setting = Setting::where('item', $item)->firstOrFail();
        
        $validated = $request->validate([
            'value' => 'required|string',
        ]);

        $setting->update(['current_value' => $validated['value']]);

        return response()->json([
            'success' => true,
            'message' => 'Setting value updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Remove the specified setting from storage.
     *
     * @param  string  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy($item)
    {
        $setting = Setting::where('item', $item)->firstOrFail();
        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Get all settings by group.
     *
     * @param  string  $group
     * @return \Illuminate\Http\Response
     */
    public function byGroup($group)
    {
        $settings = Setting::where('group', $group)
                          ->orderBy('sort_order')
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get all settings as key-value pairs.
     *
     * @return \Illuminate\Http\Response
     */
    public function allSettings()
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->item => $setting->current_value ?? $setting->default_value];
        });

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Bulk update settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.item' => 'required|string|exists:settings,item',
            'settings.*.value' => 'required|string',
        ]);

        $updated = [];
        
        foreach ($validated['settings'] as $settingData) {
            $setting = Setting::where('item', $settingData['item'])->first();
            if ($setting) {
                $setting->update(['current_value' => $settingData['value']]);
                $updated[] = $setting->item;
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($updated) . ' settings updated',
            'updated_items' => $updated,
        ]);
    }
}
