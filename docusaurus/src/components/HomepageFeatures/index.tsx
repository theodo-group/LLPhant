import React from 'react';
import clsx from 'clsx';
import styles from './styles.module.css';

type FeatureItem = {
    title: string;
    description: JSX.Element;
    image?: string;
    Svg?: React.FunctionComponent<React.SVGProps<SVGSVGElement>>;
};

const FeatureList: FeatureItem[] = [
    {
        title: 'As Simple as Possible',
        image: require('@site/static/img/llphant-logo-transparent.png').default,
        description: (
            <>
                We designed this framework to be as simple as possible, while still providing you with the tools you
                need to build powerful apps. It is compatible with Symfony and Laravel.
            </>
        ),
    },
    {
        title: 'OpenAI is supported',
        Svg: require('@site/static/img/openai.svg').default,
        description: (
            <>
                For the moment only OpenAI is supported, if you want to use other LLMs, you can use genossGPT as a
                proxy.
            </>
        ),
    },
    {
        title: 'Tanks to',
        Svg: require('@site/static/img/thanks.svg').default,
        description: (
            <>
                We want to thank few amazing projects that we use here or inspired us: <br/>
                ⭐ the learnings from using LangChain and LLamaIndex <br/>
                ⭐️ the excellent work from the OpenAI PHP SDK.
            </>
        ),
    },
];

function Feature({title, description, image, Svg}: FeatureItem) {
    return (
        <div className={clsx('col col--4')}>
            <div className="text--center">
                {image && <img className={styles.featureImage} src={image} alt={title}/>}
                {Svg && <Svg className={styles.featureSvg}/>}
            </div>
            <div className="text--center padding-horiz--md">
                <h3>{title}</h3>
                <p>{description}</p>
            </div>
        </div>
    );
}

export default function HomepageFeatures(): JSX.Element {
    return (
        <section className={styles.features}>
            <div className="container">
                <div className="row">
                    {FeatureList.map((props, idx) => (
                        <Feature key={idx} {...props} />
                    ))}
                </div>
            </div>
        </section>
    );
}
