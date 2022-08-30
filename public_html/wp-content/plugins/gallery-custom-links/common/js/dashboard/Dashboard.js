// React & Vendor Libs
const { useState, useEffect } = wp.element;
import useSWR from 'swr';

// NekoUI
import { NekoTypo, NekoPage, NekoHeader, NekoWrapper, NekoTab, NekoTabs, NekoBlock, NekoButton,
  NekoColumn, NekoSettings, NekoCheckboxGroup, NekoCheckbox } from '@neko-ui';
import { postFetch, jsonFetcher } from '@neko-ui';

import { apiUrl, restUrl, pluginUrl, restNonce } from '@app/settings';
import { SpeedTester } from './SpeedTester';
import { TabText, StyledPluginBlock, StyledPluginImage, 
  StyledPhpErrorLogs, StyledPhpInfo } from './Dashboard.styled';

if ( !apiUrl || !restUrl || !pluginUrl ) {
  console.error("[@common/dashboard] apiUrl, restUrl and pluginUrl are mandatory.");
}

const CommonApiUrl = `${restUrl}/meow-common/v1`;

const jsxTextStory = 
  <TabText>
    <NekoTypo p>
      Meow Apps is run by Jordy Meow, a photographer and software developer living in Japan (and taking <a target="_blank" href="https://offbeatjapan.org">a lot of photos</a>). Meow Apps proposes a suite of plugins focusing on photography, imaging, optimization and SEO. The ultimate goal is to make your website better, faster, while making it easy. Meow Apps also teams up with the best players in the community. For more information, please check <a href="http://meowapps.com" target="_blank">Meow Apps</a>.
    </NekoTypo>
  </TabText>;

const jsxTextPerformance = 
  <TabText>
    <NekoTypo p>
      The <b>Empty Request Time</b> helps you analyzing the raw performance of your install by giving you the average time it takes to run an empty request to your server. You can try to disable some plugins (or change their options) then Start this again to see how it influences the results. An excellent install would have an Empty Request Time of less than 500 ms. Keep it absolutely under 2,000 ms! For more information, <a href="https://meowapps.com/clean-optimize-wordpress/#Optimize_your_Empty_Request_Time" target="_blank">click here</a>.
    </NekoTypo>
    <NekoTypo p>
      <b>File Operation Time</b> creates a temporary size of 10MB every time. <b>SQL Request Time</b> counts the number of posts. Those two should be very fast, and almost the same as the <b>Empty Request Time</b>.
    </NekoTypo>
  </TabText>;

const jsxTextRecommendations = 
  <TabText>
    <NekoTypo p>
      Too many WordPress installs are blown-up with useless and/or heavy plugins, and not aware of best practices. That's not the fault of the users; WordPress pretends to be simple but it is in fact very complex, and the immensity and diversity of the community around it makes it a real jungle where everything is possible.
    </NekoTypo>
    <NekoTypo p>
      A rule of thumb is to keep your WordPress install as simple as possible, with the least number of plugins installed (run away from the heavy ones) and an excellent hosting service. Avoid VPS or self-hosted solutions; you must be a professional to actually set them up so that they are actually performant. 
    </NekoTypo>
    <NekoTypo p>
      On the Meow Apps website, you will find articles which are always updated with the latest recommendations.
      <ul>
        <li>☘️&nbsp;&nbsp;<a href="https://meowapps.com/how-to-debug-wordpress-errors/" target="_blank">How To Debug WordPress</a></li>
        <li>☘️&nbsp;&nbsp;<a href="https://meowapps.com/tutorial-improve-seo-wordpress/" target="_blank">SEO Checklist &amp; Optimization</a></li>
        <li>☘️&nbsp;&nbsp;<a href="https://meowapps.com/tutorial-faster-wordpress-optimize/" target="_blank">Optimize your WordPress Speed</a></li>
        <li>☘️&nbsp;&nbsp;<a href="https://meowapps.com/tutorial-optimize-images-wordpress/" target="_blank">Optimize Images (CDN, and so on)</a></li>
        <li>☘️&nbsp;&nbsp;<a href="https://meowapps.com/tutorial-hosting-service-wordpress/" target="_blank">Best Hosting Services for WordPress</a></li>
      </ul>
    </NekoTypo>
  </TabText>;

const swrAllSettingsKey = [`${CommonApiUrl}/all_settings/`, { headers: { 'X-WP-Nonce': restNonce } }];

const Dashboard = () => {
  const [ fatalError, setFatalError ] = useState(false);
  const { data: swrSettings, mutate: mutateSwrSettings, error: swrError } = useSWR(swrAllSettingsKey, jsonFetcher);
  const settings = swrSettings?.data;
  const hide_meowapps = settings?.meowapps_hide_meowapps;
  const force_sslverify = settings?.force_sslverify;
  const [ busy, setBusy ] = useState(false);
  const [ phpErrorLogs, setPhpErrorLogs ] = useState([]);
  const [ phpInfo, setPhpInfo ] = useState("");

  // Handle SWR errors
  useEffect(() => {
    if (swrError && !fatalError) {
      setFatalError(true);
      console.error('Error from UseSWR', swrError.message);
    }
  }, [swrError]);

  useEffect(() => {
    let info = document.getElementById('meow-common-phpinfo');
    setPhpInfo(info.innerHTML);
  }, []);

  const updateOption = async (value, id) => {
    let newSettingsData = { ...swrSettings.data };
    newSettingsData[id] = value;
    mutateSwrSettings({ ...swrSettings, data: newSettingsData }, false);
    setBusy(true);
    const res = await postFetch(`${CommonApiUrl}/update_option`, { json: { name: id, value }, nonce: restNonce });
    setBusy(false);
    if (!res.success) {
      alert(res.message);
    }
    mutateSwrSettings();
  }

  const loadErrorLogs = async () => {
    setBusy(true);
    const res = await postFetch(`${CommonApiUrl}/error_logs`, { nonce: restNonce });
    let fresh = res && res.data ? res.data : [];
    setPhpErrorLogs(fresh.reverse());
    setBusy(false);
  }

  const jsxHideMeowApps = 
  <NekoSettings title="Main Menu">
    <NekoCheckboxGroup max="1">
      <NekoCheckbox id="meowapps_hide_meowapps" label="Hide (Not Recommended)" description={<NekoTypo p>This will hide the Meow Apps Menu (on the left side) and everything it contains. You can re-enable it through though an option that will be added in Settings &rarr; General.</NekoTypo>} value="1" disabled={busy} checked={hide_meowapps} onChange={updateOption} />
    </NekoCheckboxGroup>
  </NekoSettings>;


  const jsxForceSSLVerify = 
    <NekoSettings title="SSL Verify">
      <NekoCheckboxGroup max="1">
        <NekoCheckbox id="force_sslverify" label="Force (Not Recommended)" description={<NekoTypo p>This will enforce the usage of SSL when checking the license or updating the plugin.</NekoTypo>} value="1" disabled={busy} checked={force_sslverify} onChange={updateOption} />
      </NekoCheckboxGroup>
    </NekoSettings>;

  return (
    <NekoPage showRestError={fatalError}>

      <NekoHeader title='The Dashboard'>
      </NekoHeader>

      <NekoWrapper>

        <NekoColumn full>
          
          {/* TAB FOR ADVANCED SETTINGS */}
          <NekoTabs>
            <NekoTab title='Meow Apps'>

              {jsxTextStory}

              <NekoWrapper>
                <NekoColumn minimal>

                  <StyledPluginBlock title="Media Cleaner" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/media-cleaner.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/media-cleaner/'>Media Cleaner</a></h2>
                      <p>The Cleaner analyzes your WordPress entirely to find out which files are not used. You can trash them, before deleting them permanently. Your WordPress will breath again :)</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Database Cleaner" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/database-cleaner.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/database-cleaner/'>Database Cleaner</a></h2>
                      <p>Clean your WordPress database. This plugin is simple, handles risk levels, and aims to work even on oversized databases on which other plugins fail to operate.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Media File Renamer" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/media-file-renamer.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/media-file-renamer/'>Media File Renamer</a></h2>
                      <p>The Renamer will help you in getting nicer filenames for an improved SEO and a tidier filesystem. It's mostly automatic and very fun to use.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Social Engine" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/social-engine.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/social-engine/'>Social Engine</a></h2>
                      <p>Organize, schedule and automate the publishing of your content and photos on social accounts. Similar to Buffer, TweetDeck, etc, but without all the limitations, and in your WordPress.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Meow Analytics" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/meow-analytics.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/meow-analytics/'>Meow Analytics</a></h2>
                      <p>Are you tired of those heavy plugins, accessing your Google Analytics deliberately? Switch to Meow Analytics!</p>
                    </div>
                  </StyledPluginBlock>
                  
                </NekoColumn>

                <NekoColumn minimal>

                <StyledPluginBlock title="Photo Engine" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/wplr-sync.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/wplr-sync/'>Photo Engine</a></h2>
                      <p>Are you using Lightroom? So you know Photo Engine already. Wait, you don't? You must try it! This plugin will be your favorite very soon.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Meow Gallery" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/meow-gallery.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/meow-gallery/'>Meow Gallery</a></h2>
                      <p>This is the fastest gallery system... and it is pretty as well! It is 100% compatible with the native WordPress galleries and therefore, works right away.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Meow Lightbox" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/meow-lightbox.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/meow-lightbox/'>Meow Lightbox</a></h2>
                      <p>A very sleek and performant Lightbox which will also display your EXIF data (camera, lens, aperture...). Photographers love it.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Perfect Images (Retina)" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/wp-retina-2x.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/wp-retina-2x/'>Perfect Images + Retina</a></h2>
                      <p>It handles Retina, help you managing the Image Sizes registered in your WP, and much more.</p>
                    </div>
                  </StyledPluginBlock>

                  <StyledPluginBlock title="Contact Form Block" className="primary">
                    <StyledPluginImage src={`${pluginUrl}/common/img/contact-form-block.png`} />
                    <div>
                      <h2><a target='_blank' href='https://wordpress.org/plugins/contact-form-block/'>Contact Form Block</a></h2>
                      <p>A simple, pretty and superlight contact form. If you simply want your visitors to get in touch with you, this contact form will be perfect for you and your WordPress.</p>
                    </div>
                  </StyledPluginBlock>

                </NekoColumn>

              </NekoWrapper>
            </NekoTab>
          
            <NekoTab title="Performance">
              {jsxTextPerformance}
              <div style={{ display: 'flex', justifyContent: 'space-around', marginBottom: 25 }}>
                <SpeedTester title="Empty Request Time" request="empty_request" max={2500} />
                <SpeedTester title="File Operation Time" request="file_operation" max={2600} />
                <SpeedTester title="SQL Request Time" request="sql_request" max={2800} />
              </div>
            </NekoTab>

            <NekoTab title="Recommendations">
              {jsxTextRecommendations}
            </NekoTab>

            <NekoTab title="PHP Info">
              <StyledPhpInfo dangerouslySetInnerHTML={{ __html: phpInfo }} />
            </NekoTab>

            <NekoTab title="PHP Error Logs">
              <TabText>
                <NekoButton style={{ marginBottom: 10 }} color={'#ccb027'} onClick={loadErrorLogs}>
                    Load PHP Error Logs
                </NekoButton>
                <StyledPhpErrorLogs>
                  {phpErrorLogs.map(x => <li class={`log-${x.type}`}>
                    <span class='log-type'>{x.type}</span>
                    <span class='log-date'>{x.date}</span>
                    <span class='log-content'>{x.content}</span>
                  </li>)}
                </StyledPhpErrorLogs>
              </TabText>
              {/* {jsxPhpErrorLogs}
              <StyledPhpErrorLogs dangerouslySetInnerHTML={{ __html: phpErrorLogs }} />
              <StyledPhpInfo dangerouslySetInnerHTML={{ __html: phpInfo }} /> */}
            </NekoTab>

            <NekoTab title="Settings">
              <NekoBlock title="Settings" className="primary">
                {jsxHideMeowApps}
                {jsxForceSSLVerify}
              </NekoBlock>
            </NekoTab>
          
          </NekoTabs>

        </NekoColumn>

      </NekoWrapper>
    </NekoPage>
  );
};

export { Dashboard };