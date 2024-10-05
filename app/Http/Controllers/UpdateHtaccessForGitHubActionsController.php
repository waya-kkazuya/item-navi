<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class UpdateHtaccessForGitHubActionsController extends Controller
{
    public function update()
    {
        $client = new Client();
        $response = $client->get('https://api.github.com/meta');
        $data = json_decode($response->getBody(), true);

        $actions_ips = $data['actions'];

        $htaccess_content = "<RequireAll>\n";
        foreach ($actions_ips as $ip) {
            // サブネットマスクを削除
            $ip_without_mask = explode('/', $ip)[0];
            \Log::info($ip_without_mask);
            $htaccess_content .= "  Require ip {$ip_without_mask}\n";
        }
        $htaccess_content .= "</RequireAll>\n";

        \Log::info($htaccess_content);
        
        $htaccess_path = public_path('.htaccess');
        $existing_htaccess = file_get_contents($htaccess_path);

        $new_htaccess = preg_replace('/<RequireAll>.*<\/RequireAll>/s', $htaccess_content, $existing_htaccess);

        file_put_contents($htaccess_path, $new_htaccess);

        return response()->json(['message' => 'Updated .htaccess with new IP addresses.']);
    }
}
