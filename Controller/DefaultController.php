<?php

namespace OIST\WebAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use IMAG\LdapBundle\Event\LdapUserEvent;
use IMAG\LdapBundle\User\LdapUser;
use IMAG\LdapBundle\Provider\LdapAuthenticationProvider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
//     public function indexAction()
//     {    	
//     	$session = $this->container->get('session');
//     	$theLdapManagerUser=$session->get('CurrentUser');
//     	if($theLdapManagerUser->getDn()){
//     		echo "already authentified :)";
//     	}else{
//     		echo "sorry. this bundle sucks";
//     	}    	
//         return $this->render('OISTTestBundle:Test:index.html.twig');
//     }
    
    public function authentAction(){
    	
    	//the user is already authenticated by webauth, I dont want to auth again, I just want to create a new instance of it.
    	//so no LdapAuthenticationProvider->Autheticate() needed, that's what I am doing here.
    	$serverparams=$this->get('request')->server;
    	$currentid=false;
    	if($serverparams->has('WEBAUTH_USER')){
    		$currentid=$serverparams->get('WEBAUTH_USER');
    	}
    	if($serverparams->has('REDIRECT_WEBAUTH_USER')){
    		$currentid=$serverparams->get('REDIRECT_WEBAUTH_USER');
    	}

    	//It takes a manual change of url to set the webauth variables, lets redirect to the same action until it gets the session variables from webauth    	
    	if(!$currentid){
    		return $this->redirect($this->generateUrl('bouncing'));
    	}
    	
    	
    	$theLdapManagerUser = $this->container->get('imag_ldap.ldap_manager');

    	$theLdapManagerUser->setUsername($currentid);
    	$theLdapManagerUser->exists($currentid);
    	$theLdapManagerUser->auth();

    	$roles=$theLdapManagerUser->getRoles();
    	$dn=$theLdapManagerUser->getDn();
    	// we got the user, now lets automatically authenticate it   
    	$providerKey= $this->container->getParameter('oist_web_auth.firewall_name');	
    	$token = new UsernamePasswordToken($theLdapManagerUser->getUsername(), null, $providerKey, $roles);
    	$this->container->get('security.context')->setToken($token);    	
    	//I need that to have easy access to the user
    	$this->get('session')->set('CurrentUser',$theLdapManagerUser);

	return $this->redirect($this->generateUrl('_homeafterlogin'));
    	return $this->render('OISTWebAuthBundle:Default:index.html.twig', array(
    			'roles' => $roles,
    			'dn'=>$dn    			
    			));
    }
    
    public function bouncingAction(){    	
    	return $this->redirect($this->generateUrl('login'));
    }    
}
