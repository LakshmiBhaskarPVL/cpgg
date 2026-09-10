<?php

namespace Tests\Feature;

use App\Exceptions\Pterodactyl\PterodactylConnectionException;
use App\Exceptions\Pterodactyl\PterodactylNotFoundException;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\ServerLimitReachedException;
use App\Exceptions\Payment\InvoiceException;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class TestExceptionRendering extends TestCase
{
    use CreatesApplication;

    /**
     * Verify the exception handler maps a PterodactylNotFoundException to a
     * 404 JSON response.
     *
     * @return void
     */
    public function test_pterodactyl_not_found_renders_404(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new PterodactylNotFoundException());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('Resource does not exist', $response->getContent());
    }

    /**
     * Verify the handler renders the matching web error page for a
     * PterodactylNotFoundException on non-JSON requests (instead of a generic
     * 500 as would happen for an unhandled exception).
     *
     * @return void
     */
    public function test_pterodactyl_not_found_renders_web_error_page(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/servers', 'GET');

        $response = $handler->render($request, new PterodactylNotFoundException());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('Not Found', $response->getContent());
        $this->assertStringNotContainsString('"message"', $response->getContent());
    }

    /**
     * Verify the exception handler maps a PterodactylConnectionException
     * (which carries no HTTP status code) to a 500 JSON response instead of
     * crashing on an invalid status code.
     *
     * @return void
     */
    public function test_pterodactyl_connection_renders_500(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new PterodactylConnectionException());

        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString('Unable to connect', $response->getContent());
    }

    /**
     * Verify the exception handler maps an InsufficientCreditsException to a
     * 422 JSON response.
     *
     * @return void
     */
    public function test_insufficient_credits_renders_422(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new InsufficientCreditsException());

        $this->assertSame(422, $response->getStatusCode());
    }

    /**
     * Verify the exception handler maps a ServerLimitReachedException to a
     * 422 JSON response.
     *
     * @return void
     */
    public function test_server_limit_reached_renders_422(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new ServerLimitReachedException());

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_invoice_exception_renders_404_json(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/admin/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render(
            $request,
            new InvoiceException('Invoice not found', 404)
        );

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        $this->assertSame(
            ['message' => 'Invoice not found'],
            $response->getData(true)
        );
    }

    public function test_invoice_exception_renders_web_error_page(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/admin/test', 'GET');

        $response = $handler->render(
            $request,
            new InvoiceException('Invoice not found', 404)
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
