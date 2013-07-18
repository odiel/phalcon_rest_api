<?

namespace Modules\Error;

class NotFound extends \Classes\Application\Controller
{

    public function get()
    {
        $this->reply->notFound('The requested resource was not found.');
    }

    public function post()
    {
        $this->reply->notFound('The requested resource was not found.');
    }

    public function put()
    {
        $this->reply->notFound('The requested resource was not found.');
    }

    public function delete()
    {
        $this->reply->notFound('The requested resource was not found.');
    }

}