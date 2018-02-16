<?php

namespace App\Http\Controllers\Backend;

use Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Notifications\UserBlockedNotification;
use App\Notifications\UserActiveNotification;
use App\Repositories\UserRepository;
use App\Http\Controllers\Controller;

/**
 * Ban controller 
 * 
 * De controller op gebruikers te blokkeren en te activeren in het systeem. 
 * 
 * @author      Tim Joosten <tim@activisme.be>
 * @copyright   2018 Tim Joosten
 * @package     \App\Http\Controllers\Backend
 */
class BanController extends Controller
{
    /** @var \App\Repositories\UserRepository $user */
    private $user; 

    /**
     * BanController constructor 
     *
     * @todo Implementatie role middleware (admin role needed)
     *
     * @param  UserRepository $user    De abstractie laag tussen controller en databank.  
     * @return void
     */
    public function __construct(UserRepository $user) 
    {
        $this->middleware(['role:admin', 'forbid-banned-user']);
        $this->user = $user;
    }

    /**
     * Blokkeer een gebruiker in de applicatie. 
     * 
     * @todo Implementatie PHPUnit test case. 
     * 
     * @param  int $user De unieke identificatie van de gebruiker in de databank.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function lock(int $user): RedirectResponse 
    {
        $user = $this->user->findOrFail($user);

        if (Gate::denies('same-user', $user)) {                 // 1) De gebruiker is niet dezelfde gebruiker dan opgegeven
            if ($user->ban(['expired_at' => '+2 weeks'])) {     // 2) De gebruiker is voor 2 weken geband in het systeem.
                $user->notify((new UserBlockedNotification(auth()->user()))->delay(now()->addMinute()));

                $this->registerActivity($user, trans('users.flash-success-account-locked'));
                $flash = flash(trans('users.activities-lock', ['name' => ucfirst($user->name)]))->info();
            }
             
        } else { $flash = flash(trans('users.flash-error-block-same-user'))->error(); }

        $flash->important(); // Gebruiker moet de alert manueel wegklikken uit UX overwegingen.
        return redirect()->route('admin.users.index');
    }

    /**
     * Activieer een gebruiker in de databank. 
     * 
     * @todo Implementatie PHPUnit test case
     * @todo mail notificatie
     *
     * @param  int $user De unieke identificatie van de gebruiker in de databank.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlock(int $user): RedirectResponse 
    {
        $user = $this->user->findOrFail($user);

        if (Gate::denies('same-user', $user)) {   // 1) De gebruiker is niet dezelfde gebruiker dan opgegeven. 
            if ($user->isBanned()) {              // 2) De gebruiker is geactiveerd. 
                $user->unban();               
                $user->notify((new UserActiveNotification(auth()->user()))->delay(now()->addMinute()));
                
                $userInfo = ['name' => ucfirst($user->name)];

                $this->registerActivity($user, trans('ban.activities.unban', $userInfo));
                $flash = flash(trans('ban.flash.success-unban-user', $userInfo))->info();
            } 
        } else { $flash = flash(trans('ban.flash.error-unban-same-user'))->error(); }

        $flash->important();
        return redirect()->route('admin.users.index');
    }
}
