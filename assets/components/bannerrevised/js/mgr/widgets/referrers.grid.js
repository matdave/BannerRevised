bannerrev.grid.Referrers = function (config) {
    config = config || {};
    Ext.applyIf(
        config,{
            id: 'bannerrevised-grid-referrers'
            ,url: bannerrev.config.modx3 ?
                MODx.config.connector_url :
                bannerrev.config.connector_url
            ,baseParams: { action: bannerrev.config.modx3 ?
                    'BannerRevised\\v3\\Processors\\Clicks\\GetReferrers' :
                    'mgr/clicks/getreferrers', period: ''}
            ,fields: ['referrer', 'clicks']
            ,paging: true
            ,border: false
            ,frame: false
            ,remoteSort: false
            ,anchor: '97%'
            ,autoExpandColumn: 'referrer'
            ,columns: [{
                header: _('bannerrevised.stats.referrer')
                ,dataIndex: 'referrer'
                ,sortable: true
            },{
                header: _('bannerrevised.stats.clicks')
                ,dataIndex: 'clicks'
                ,sortable: false
            }]
            ,tbar: [{
                xtype: 'modx-combo'
                ,id: 'bannerrevised-referrers-period'
                ,mode: 'local'
                ,store: new Ext.data.SimpleStore(
                    {
                        fields: ['d','v']
                        ,data: [[_('bannerrevised.stats.overall', '')],[_('bannerrevised.stats.today'),'%Y-%m-%d'],[_('bannerrevised.stats.thismonth'),'%Y-%m'],[_('bannerrevised.stats.lastmonth'),'last month'],[_('bannerrevised.stats.thisyear'),'%Y']]
                    }
                )
            ,displayField: 'd'
            ,valueField: 'v'
            ,lazyRender: false
            ,listeners: {
                'select': {fn:this.setPeriod,scope:this}
                }
            }]
        }
    );
    bannerrev.grid.Referrers.superclass.constructor.call(this,config)
};
Ext.extend(
    bannerrev.grid.Referrers,MODx.grid.Grid,{
        setPeriod: function (tf,nv,ov) {
            var s = this.getStore();
            s.baseParams.period = tf.getValue();
            this.getBottomToolbar().changePage(1);
            this.refresh();
        }
    }
);
Ext.reg('bannerrevised-grid-referrers',bannerrev.grid.Referrers);