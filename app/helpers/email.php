<?php 

namespace app\helpers;

use app\models\empresa;
use app\models\main\empresaModel;
use PHPMailer\PHPMailer\PHPMailer;

class email{

    private PHPMailer $email;
    private empresa $empresa;
    private array $emailsTo = [];
    private array $emailsCc = [];
    private array $emailsBcc = [];
    private bool $from = false;

    public function __construct()
    {
        $this->email = new PHPMailer(true);
        $this->empresa = empresaModel::get(1);
    }

    public function addEmailCc(...$emails):email
    {
        $this->emailsCc = array_merge($this->emailsCc,$emails);
        return $this;
    }
    
    public function addEmailBcc(...$emails):email
    {
        $this->emailsBcc = array_merge($this->emailsBcc,$emails);
        return $this;
    }

    public function addEmail(...$emails):email
    {
        if(!$this->emailsTo){
            if(is_array($emails[0]))
                $this->email->addReplyTo($emails[0][0],$emails[0][1]);
            else
                $this->email->addReplyTo($emails[0]);    
        }

        $this->emailsTo = array_merge($this->emailsTo,$emails);
        return $this;
    }

    public function setFrom(string $email,string $nome = "Site"){
        $this->email->setFrom($email,$nome);
        $this->from = true;
        return $this;
    }

    public function send($assunto,$mensagem,$isHtml = false):bool
    {
        if(!$this->empresa->smtp_servidor || !$this->empresa->smtp_port){
            return false;
        }

        $this->email->CharSet = "UTF-8";
        $this->email->setLanguage("pt_br");
        $this->email->isSMTP();
        $this->email->Host = $this->empresa->smtp_servidor;
        if($this->empresa->smtp_usuario && $this->empresa->smtp_senha)
        {
            $this->email->SMTPAuth    = true;
            $this->email->Username    = $this->empresa->smtp_usuario;
            $this->email->Password    = $this->empresa->smtp_senha;
        }

        if($this->empresa->smtp_encryption)
        {
            $this->email->SMTPSecure  = $this->empresa->smtp_encryption;
        }
            
        $this->email->Port = $this->empresa->smtp_port;

        if(!$this->from)
            $this->email->setFrom($this->empresa->contato_email, $this->empresa->nome);

        if(!$this->emailsTo){
            $this->email->addAddress($this->empresa->contato_email, $this->empresa->nome);
        }

        foreach ($this->emailsTo as $email){
            if(is_array($email) && isset($email[1])){
                $this->email->addAddress($email[0], $email[1]);  
            }
            
            if(!is_array($email)){
                $this->email->addAddress($email);  
            }
        }

        foreach ($this->emailsCc as $email){
            $this->email->addCC($email);  
        }

        foreach ($this->emailsBcc as $email){
            $this->email->addBCC($email);  
        }

        
        $this->email->Subject = $assunto;
        $this->email->isHTML($isHtml);
        $this->email->Body = $mensagem;

        if (!$this->email->send()) {
            mensagem::setErro("Erro ao enviar email");
            return false;
        } 

        mensagem::setSucesso("Email enviado com sucesso");
        return true;
    }
}

?>
