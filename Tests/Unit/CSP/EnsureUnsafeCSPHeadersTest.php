<?php

declare(strict_types=1);

namespace Supseven\ThemeBase\Tests\Unit\CSP;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Supseven\ThemeBase\CSP\EnsureUnsafeCSPHeaders;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Page\ResourceHashCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Configuration\Behavior;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Configuration\DispositionConfiguration;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\ConsumableNonce;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\DirectiveHashCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Disposition;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Event\PolicyMutatedEvent;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Middleware\PolicyBag;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Policy;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\SourceKeyword;
use TYPO3\CMS\Core\Type\Map;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[CoversClass(EnsureUnsafeCSPHeaders::class)]
class EnsureUnsafeCSPHeadersTest extends TestCase
{
    #[Test]
    public function noActionIfNoRequestObject(): void
    {
        $policy = $this->createMock(Policy::class);
        $policy->expects($this->never())->method($this->anything());

        $event = new PolicyMutatedEvent(Scope::frontend(), null, $policy, $policy);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);
    }

    #[Test]
    public function noActionIfNoTypoScript(): void
    {
        $request = new ServerRequest('GET', new Uri('https://example.com/'));
        $policy = $this->createMock(Policy::class);
        $policy->expects($this->never())->method($this->anything());

        $event = new PolicyMutatedEvent(Scope::frontend(), $request, $policy, $policy);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);
    }

    #[Test]
    public function noActionIfNoSetting(): void
    {
        $typoscript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoscript->setConfigArray(['allow_unsafe_csp' => '0']);

        $request = new ServerRequest('GET', new Uri('https://example.com/'))
            ->withAttribute('frontend.typoscript', $typoscript);
        $policy = $this->createMock(Policy::class);
        $policy->expects($this->never())->method($this->anything());

        $event = new PolicyMutatedEvent(Scope::frontend(), $request, $policy, $policy);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);
    }

    #[Test]
    public function invalidDirectiveThrowsException(): void
    {
        $typoscript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoscript->setConfigArray(['allow_unsafe_csp' => 'invalid-csp-directive ']);

        $request = new ServerRequest('GET', new Uri('https://example.com/'))
            ->withAttribute('frontend.typoscript', $typoscript);
        $policy = $this->createMock(Policy::class);
        $policy->expects($this->never())->method($this->anything());

        $event = new PolicyMutatedEvent(Scope::frontend(), $request, $policy, $policy);

        $this->expectException(\UnexpectedValueException::class);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);
    }

    #[Test]
    public function booleanSetsDefaultDirectives(): void
    {
        $typoscript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoscript->setConfigArray(['allow_unsafe_csp' => '1']);

        $request = new ServerRequest('GET', new Uri('https://example.com/'))
            ->withAttribute('frontend.typoscript', $typoscript);

        $policy = new Policy();
        $policy->set(Directive::DefaultSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $policy->set(Directive::StyleSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $policy->set(Directive::StyleSrcElem, SourceKeyword::self, SourceKeyword::nonceProxy);

        $event = new PolicyMutatedEvent(Scope::frontend(), $request, $policy, $policy);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);

        $expected = new Policy();
        $expected->set(Directive::DefaultSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $expected->set(Directive::StyleSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $expected->set(Directive::StyleSrcElem, SourceKeyword::self, SourceKeyword::unsafeInline);
        $expected->set(Directive::ScriptSrcElem, SourceKeyword::self, SourceKeyword::unsafeInline);
        $expected->set(Directive::StyleSrcAttr, SourceKeyword::self, SourceKeyword::unsafeInline);

        $this->assertEquals($expected->compile($this->emptyPolicyBag()), $policy->compile($this->emptyPolicyBag()));
    }

    #[Test]
    public function listSetsSpecificDirectives(): void
    {
        $typoscript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoscript->setConfigArray(['allow_unsafe_csp' => 'script-src-elem, style-src-attr']);

        $nonce = new ConsumableNonce('1234');

        $request = new ServerRequest('GET', new Uri('https://example.com/'))
            ->withAttribute('frontend.typoscript', $typoscript);

        $policy = new Policy();
        $policy->set(Directive::DefaultSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $policy->set(Directive::StyleSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $policy->set(Directive::StyleSrcElem, SourceKeyword::self, SourceKeyword::nonceProxy);

        $event = new PolicyMutatedEvent(Scope::frontend(), $request, $policy, $policy);

        $subject = new EnsureUnsafeCSPHeaders();
        $subject($event);

        $expected = new Policy();
        $expected->set(Directive::DefaultSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $expected->set(Directive::StyleSrc, SourceKeyword::self, SourceKeyword::nonceProxy);
        $expected->set(Directive::StyleSrcElem, SourceKeyword::self, SourceKeyword::nonceProxy);
        $expected->set(Directive::ScriptSrcElem, SourceKeyword::self, SourceKeyword::unsafeInline);
        $expected->set(Directive::StyleSrcAttr, SourceKeyword::self, SourceKeyword::unsafeInline);

        $this->assertEquals($expected->compile($this->emptyPolicyBag()), $policy->compile($this->emptyPolicyBag()));
    }

    protected function emptyPolicyBag(): PolicyBag
    {
        $scope = Scope::frontend();
        $disposition = Disposition::enforce;
        $dispositionMap = new Map();
        $dispositionMap[$disposition] = new DispositionConfiguration(
            true,
            true,
            'https://www.domain.tld/@report-csp-error',
        );
        $behaviour = new Behavior();
        $nonce = new ConsumableNonce('1234');
        $collection = new DirectiveHashCollection(new ResourceHashCollection(
            new NullLogger(),
            $this->createStub(\TYPO3\CMS\Core\SystemResource\SystemResourceFactory::class),
            new VariableFrontend('hash', new TransientMemoryBackend()),
        ));

        return new PolicyBag(
            $scope,
            $dispositionMap,
            $behaviour,
            $nonce,
            $collection,
        );
    }
}
