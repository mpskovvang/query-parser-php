<?php

namespace Gdbots\QueryParser\Visitor;

use Elastica\Filter\Query as FilterQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Gdbots\QueryParser\Node;
use Gdbots\QueryParser\QueryLexer;

class QueryItemElastica implements QueryItemVisitorInterface
{
    /**
     * {@inheritDoc}
     */
    public function visitWord(Node\Word $word)
    {
        $query = new QueryString($word->getToken());

        if ($word->isBoosted()) {
            $query->setBoost($word->getBoostBy());
        }
        if ($word->isExcluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMustNot($query);
            $query = $boolQuery;
        } elseif ($word->isIncluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust($query);
            $query = $boolQuery;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitPhrase(Node\Phrase $phrase)
    {
        $query = new QueryString($phrase->getToken());

        if ($phrase->isBoosted()) {
            $query->setBoost($phrase->getBoostBy());
        }
        if ($phrase->isExcluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMustNot($query);
            $query = $boolQuery;
        } elseif ($phrase->isIncluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust($query);
            $query = $boolQuery;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitUrl(Node\Url $url)
    {
        $query = new QueryString($url->getToken());

        if ($url->isBoosted()) {
            $query->setBoost($url->getBoostBy());
        }
        if ($url->isExcluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMustNot($query);
            $query = $boolQuery;
        } elseif ($url->isIncluded()) {
            $boolQuery = new BoolQuery();
            $boolQuery->addMust($query);
            $query = $boolQuery;
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitHashtag(Node\Hashtag $hashtag)
    {
        // todo:
    }

    /**
     * {@inheritDoc}
     */
    public function visitMention(Node\Mention $mention)
    {
        // todo:
    }

    /**
     * {@inheritDoc}
     */
    public function visitExplicitTerm(Node\ExplicitTerm $term)
    {
        if ($term->getNominator() instanceof Node\AbstractSimpleTerm) {
            $operator = 'value';

            switch ($term->getTokenTypeText()) {
                case ':>':
                    $operator = 'gte';
                    break;

                case ':<':
                    $operator = 'lt';
                    break;
            }

            $query = new Term([$term->getNominator()->getToken() => [$operator => $term->getTerm()->getToken()]]);

            if ($term->isBoosted()) {
                $query->addParam('boost', $term->getBoostBy());
            }
            if ($term->isExcluded()) {
                $boolQuery = new BoolQuery();
                $boolQuery->addMustNot($query);
                $query = $boolQuery;
            } elseif ($term->isIncluded()) {
                $boolQuery = new BoolQuery();
                $boolQuery->addMust($query);
                $query = $boolQuery;
            }

            return $query;
        }

        $method = sprintf('visit%s', ucfirst(substr(get_class($term->getNominator()), 24)));
        if (method_exists($this, $method)) {
            return $this->$method($term->getNominator());
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function visitSubExpression(Node\SubExpression $sub)
    {
        return $sub->getExpression()->accept($this);
    }

    /**
     * {@inheritDoc}
     */
    public function visitOrExpressionList(Node\OrExpressionList $list)
    {
        $query = new BoolQuery();

        foreach ($list->getExpressions() as $expression) {
            if ($q = $expression->accept($this)) {
                $query->addShould($q);
            }
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function visitAndExpressionList(Node\AndExpressionList $list)
    {
        $query = new BoolQuery();

        foreach ($list->getExpressions() as $expression) {
            if ($q = $expression->accept($this)) {
                $query->addMust($q);
            }
        }

        return $query;
    }
}
